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
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Router;

class BetController extends BaseController
{
    public function createBet(Request $request, Response $response)
    {
        $selectStatement = $this->database->select()
            ->from('answerType');

        $answerTypes = $selectStatement->execute()->fetchAll();

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
                $this->addBet($create);

                /** @var Router $router */
                $router = $this->container->get('router');
                return $response->withRedirect($router->pathFor('Home'));
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
            return true;
        } else {
            throw new \Exception('Couldnt create ' . json_encode($data));
        }
    }

    private function isBetInProgress()
    {
        $lastBet = $this->database->select()
            ->from('bet')
            ->orderBy('dateCreated', 'DESC')
            ->limit(1, 0);

        $lastBet = $lastBet->execute()->fetch();

        if (empty($lastBet)) {
            return false;
        }

        $dateCreated = new \DateTime($lastBet['dateCreated']);
        $interval = new \DateInterval('PT' . $lastBet['pariDurationMinute'] . 'M');

        $dateEnd = $dateCreated->add($interval);

        return $dateEnd > new \DateTime();
    }
}