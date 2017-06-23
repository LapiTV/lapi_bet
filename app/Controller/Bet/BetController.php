<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 21/06/2017
 * Time: 15:07
 */

namespace Bet\App\Controller\Bet;


use Bet\App\Controller\BaseController;
use Bet\App\Exception\FormException;
use Bet\App\Manager;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

class BetController extends BaseController
{
    public function listBetAction(Request $request, Response $response)
    {
        $answerTypes = Manager\AnswerType::getAll();

        $tmp = [];
        foreach ($answerTypes as $answerType) {
            $tmp[$answerType['id']] = $answerType['name'];
        }
        $answerTypes = $tmp;

        $bets = Manager\Bet::getAll(['dateCreated' => 'DESC']);

        foreach ($bets as $key => $bet) {
            $bets[$key]['inProgress'] = $this->isBetInProgress($bet['id']);
            $bets[$key]['answerType'] = $answerTypes[$bet['answerTypeId']] ?? '';

            $selectStatement = $this->database->select(['COUNT(*) as nbre'])
                ->from('vote')
                ->where('betId', '=', $bet['id']);
            $votesNbr = $selectStatement->execute()->fetch();

            $bets[$key]['voteNumber'] = $votesNbr['nbre'] ?? 0;
        }

        $this->view->render($response, 'listBet.html.twig', [
            'bets' => $bets,
        ]);
    }

    public function displayBetAction(Request $request, Response $response, $args)
    {
        $bet = Manager\Bet::get($args['betId']);

        if(empty($bet)) {
            /** @var Router $router */
            $router = $this->container->get('router');
            return $response->withRedirect($router->pathFor('List_Bet'));
        }

        $bet['answerType'] = Manager\AnswerType::get($bet['answerTypeId']);

        return $this->view->render($response, 'displayBet.html.twig', [
            'bet' => $bet,
            'inProgress' => $this->isBetInProgress($bet['id']),
        ]);
    }

    public function ajaxGetDataBet(Request $request, Response $response, $args)
    {
        $bet = Manager\Bet::get($args['betId']);

        if(empty($bet)) {
            return $response->withJson(['error' => 404]);
        }

        if (!Manager\User::isLogin()) {
            $lastBet = Manager\Bet::getLastBet();

            if(empty($lastBet) || $lastBet['id'] != $bet['id']) {
                return $response->withJson(['error' => 401]);
            }
        }

        // Here I have a bet, that I'm allow to use, and that exists
        $votes = Manager\Vote::getVoteOf($bet['id']);

        $answerType = Manager\AnswerType::get($bet['answerTypeId']);

        $result = [];
        foreach($votes as $vote) {
            $key = Manager\AnswerType::parseMessage($answerType['type'], $vote['answer']);

            if($key === false) {
                continue;
            }

            if(empty($result[$key])) {
                $result[$key] = 1;
            } else {
                $result[$key] += 1;
            }
        }

        $result = array_filter($result, function($n) {
            return $n > Manager\Vote::THRESHOLD_DISPLAY;
        });

        ksort($result);

        return $response->withJson(['key' => array_keys($result), 'series' => array_values($result)]);
    }

    public function createBet(Request $request, Response $response)
    {
        $answerTypes = Manager\AnswerType::getAll();

        $betInProgress = $this->isBetInProgress();

        if ($betInProgress) {
            $error = 'Un pari est en cours';
        }

        if ($request->getMethod() === 'POST') {
            $name = $request->getParam('name');
            $description = $request->getParam('description', '');
            $answerType = $request->getParam('answerType');
            $durationMinute = $request->getParam('durationMinute');

            $create = [
                'name' => $name,
                'description' => $description,
                'pariDurationMinute' => $durationMinute,
                'answerTypeId' => $answerType,
            ];

            try {
                $betId = $this->addBet($create);

                /** @var Router $router */
                $router = $this->container->get('router');
                return $response->withRedirect($router->pathFor('Display_Bet', ['betId' => $betId]));
            } catch (FormException $fe) {
                $error = $fe->getMessage();
            } catch (\Exception $e) {
                $this->logger->warning("Add Bet", [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'trace' => $e->getTrace(),
                ]);
                $error = 'Une erreur est survenue lors de la création du pari.';
            }
        }

        $this->view->render($response, 'createBet.html.twig', [
            'answerTypes' => $answerTypes,
            'error' => $error ?? '',
            'data' => $create ?? [],
        ]);
    }

    private function addBet($data)
    {
        if (empty($data['name'])) {
            throw new FormException('Le nom du pari est requis.');
        }

        if (empty($data['answerTypeId'])) {
            throw new FormException('Le type de réponse du pari est requis.');
        }

        if (empty($data['pariDurationMinute'])) {
            throw new FormException('La durée du pari est requise.');
        }

        if ($this->isBetInProgress()) {
            throw new FormException('Un pari est en cours.');
        }

        $betSameName = $this->database->select()->from('bet')->where('name', '=', $data['name']);
        $betSameName = $betSameName->execute()->fetch();
        if (!empty($betSameName)) {
            throw new FormException('Un pari existe déjà avec le même nom.');
        }

        $insert = $this->database->insert(array_keys($data))
            ->into('bet')
            ->values(array_values($data));

        $insertId = $insert->execute();

        if (!empty($insertId)) {
            return $insertId;
        } else {
            throw new \Exception('Couldnt create ' . json_encode($data));
        }
    }

    private function isBetInProgress(int $id = null)
    {
        $lastBet = Manager\Bet::getLastBet();

        if (empty($lastBet)) {
            return false;
        }

        if (!empty($id) && $lastBet['id'] != $id) {
            return false;
        }

        $dateCreated = new \DateTime($lastBet['dateCreated']);
        $interval = new \DateInterval('PT' . $lastBet['pariDurationMinute'] . 'M');

        $dateEnd = $dateCreated->add($interval);

        return $dateEnd > new \DateTime();
    }
}