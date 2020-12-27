<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Entity\StockMarketData;
use App\Entity\Stocks;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModeController extends BasicController
{
    /**
     * @Route("/settings/modes", name="modes")
     * @return Response
     */
    public function indexAction(): Response
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        $this->getModes();
        $this->data['modes'] = $this->getDoctrine()->getRepository(Settings::class)->findBy(['type' => 'mode']);
        return $this->render('settings/modes/index.html.twig', $this->data);
    }

    /**
     * @Route("/settings/modes/toggle_mode", name="toggle_mode")
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function toggleModeAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->request->get('id')) {
            $id = $request->request->get('id');
            $mode = $this->getDoctrine()->getRepository(Settings::class)->find($id);
            if (isset($mode)) {
                $parameters = $mode->getParameters();
                // actions for Stock Market mode
                if (!is_null($parameters['state']) && $mode->getName() === "Stock Market") {
                    switch ($parameters['state']) {
                        case "0":
                            // when activating the mode, create an entry in StockMarketData for each article in Stocks
                            $em = $this->getDoctrine()->getManager();
                            $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
                            $repo_smd = $this->getDoctrine()->getRepository(StockMarketData::class);
                            foreach ($repo_stocks->findAll() as $article) {
                                if (is_null($repo_smd->find($article->getId()))) {
                                    $article_data = new StockMarketData();
                                    $article_data->setArticleId($article);
                                    $article->setData($article_data);
                                    $em->persist($article);
                                } else {
                                    $article_data = $repo_smd->find($article->getId());
                                }
                                $article_data->setStockValue($article->getSellingPrice());
                                $em->persist($article_data);
                                $em->flush();
                            }
                            $parameters['state'] = 1;
                            break;
                        case "1":
                            $parameters['state'] = 0;
                            break;
                        default:
                            return $this->json(['id' => $id, 'state' => 'Error']);
                    }
                    $mode->setParameters($parameters);
                    $this->getDoctrine()->getManager()->flush();

                    $dataArray = ['id' => $id, 'state' => $mode->getParameters()['state']];
                    return $this->json($dataArray);
                }
            }
        }

        return $this->redirectToRoute('modes');
    }
}
