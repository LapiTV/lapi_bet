<?php
/**
 * Created by PhpStorm.
 * User: Francois
 * Date: 21/06/2017
 * Time: 15:07
 */

namespace Bet\App\Controller\Bet;


use Bet\App\Controller\BaseController;
use Bet\App\Exception\CustomException;
use Bet\App\Exception\FormException;
use Bet\App\Manager;
use Bet\App\Service\Database;
use Bet\App\Service\SmsNotification;
use Bet\App\Service\Util;
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

        $bets = Manager\Bet::getAll(['datecreated' => 'DESC']);

        foreach ($bets as $key => $bet) {
            $bets[$key]['inProgress'] = $this->isBetInProgress($bet['id']);
            $bets[$key]['answerType'] = $answerTypes[$bet['answertypeid']] ?? '';

            $selectStatement = $this->database->select(['COUNT(*) as nbre'])
                ->from('vote')
                ->where('betid', '=', $bet['id']);
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

        if (empty($bet)) {
            /** @var Router $router */
            $router = $this->container->get('router');
            return $response->withRedirect($router->pathFor('List_Bet'));
        }

        try {
            $time = Manager\Bet::getDateEnd($bet['id']);
        } catch (CustomException $ce) {
            $error = $ce->getMessage();
        }

        $bet['answerType'] = Manager\AnswerType::get($bet['answertypeid']);

        return $this->view->render($response, 'displayBet.html.twig', [
            'bet' => $bet,
            'dateNow' => (new \DateTime($bet['datenow']))->getTimestamp(),
            'time' => !empty($time) ? $time->getTimestamp() : null,
            'error' => $error ?? null,
            'inProgress' => $this->isBetInProgress($bet['id']),
        ]);
    }

    public function ajaxGetDataBet(Request $request, Response $response, $args)
    {
        $bet = Manager\Bet::get($args['betId']);

        if (empty($bet)) {
            return $response->withJson(['error' => 404]);
        }

        if (!Manager\User::isLogin()) {
            $lastBet = Manager\Bet::getLastBet();

            if (empty($lastBet) || $lastBet['id'] != $bet['id']) {
                return $response->withJson(['error' => 401]);
            }
        }

        // Here I have a bet, that I'm allow to use, and that exists
        $votes = Manager\Vote::getVoteOf($bet['id']);

        $answerType = Manager\AnswerType::get($bet['answertypeid']);

        $result = [];
        foreach ($votes as $vote) {
            $key = Manager\AnswerType::parseMessage($answerType['type'], $vote['answer']);

            if ($key === false) {
                continue;
            }

            if (empty($result[$key])) {
                $result[$key] = 1;
            } else {
                $result[$key] += 1;
            }
        }

        $parameter = json_decode($bet['parameter']);

        if (!empty($parameter) && !empty($parameter->roundTo) && $answerType['type'] === 'int') {
            $finalResult = [];
            // Ranges
            foreach ($result as $key => $number) {
                $range = Util::roundDownToAny($key, $parameter->roundTo);

                if (empty($finalResult[$range])) {
                    $finalResult[$range] = $number;
                } else {
                    $finalResult[$range] += $number;
                }
            }

            $result = $finalResult;
        }

        $result = array_filter($result, function ($n) {
            return $n > Manager\Vote::THRESHOLD_DISPLAY;
        });

        $result = Manager\AnswerType::order($answerType['type'], $result);

        return $response->withJson(['key' => array_keys($result), 'series' => array_values($result)]);
    }

    public function ajaxGetWinnerBet(Request $request, Response $response, $args)
    {
        $bet = Manager\Bet::get($args['betId']);

        if (empty($bet)) {
            return $response->withJson(['error' => 404]);
        }

        $answerType = Manager\AnswerType::get($bet['answertypeid'])['type'] ?? 'string';

        $correctAnswer = Manager\AnswerType::parseMessage($answerType, $request->getParam('answer'));

        $votes = Manager\Vote::getVoteOf($bet['id']);

        $res = [];

        $minDistance = null;

        foreach ($votes as $vote) {
            $userAnswer = Manager\AnswerType::parseMessage($answerType, $vote['answer']);
            $distance = Manager\AnswerType::calcDistance(
                $answerType,
                $correctAnswer,
                $userAnswer
            );

            if (!isset($minDistance)) {
                $minDistance = $distance;
            }

            if ($distance < $minDistance) {
                $minDistance = $distance;
            }

            $row = [
                'username' => $vote['username'],
                'date' => $vote['datevote'],
                'answer' => $userAnswer,
                'distance' => $distance,
                'random' => rand(0, 100),
            ];

            $res[] = $row;
        }

        usort($res, function ($a, $b) {
            $firstSort = $a['distance'] <=> $b['distance'];

            if ($firstSort === 0) {
                return $a['random'] <=> $b['random'];
            }
            return $firstSort;
        });

        $winner = $res[0]['username'];

        return $response->withJson([
            'table' => $res,
            'winner' => $winner,
            'minDistance' => $minDistance,
            'now' => Database::getTimeDatabase()
        ]);
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
            $answerType = $request->getParam('answerType');
            $durationMinute = $request->getParam('durationMinute');
            $roundTo = $request->getParam('roundTo');

            $create = [
                'name' => $name,
                'paridurationminute' => $durationMinute,
                'answertypeid' => $answerType,
            ];

            if (!empty($roundTo)) {
                $create['parameter'] = json_encode(['roundTo' => (int)$roundTo]);
            }

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

        return $this->view->render($response, 'createBet.html.twig', [
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

        if (empty($data['answertypeid'])) {
            throw new FormException('Le type de réponse du pari est requis.');
        }

        if (empty($data['paridurationminute'])) {
            throw new FormException('La durée du pari est requise.');
        }

        if ($this->isBetInProgress()) {
            throw new FormException('Un pari est en cours.');
        }

        $insert = $this->database->insert(array_keys($data))
            ->into('bet')
            ->values(array_values($data));

        $insertId = $insert->execute();
        SmsNotification::sendSms('Ajout d\'un vote ! ' . $data['name']);

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

        $timeCreated = $lastBet['timecreated'];
        $duration = $lastBet['paridurationminute'] * 60;
        $timeLeft = $duration - $timeCreated;

        return $timeLeft > 0;
    }
}