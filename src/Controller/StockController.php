<?php
// src/AppBundle/Controller/StockController.php
namespace App\Controller;

use App\Entity\DetailsTransactions;
use App\Entity\StockMarketData;
use App\Entity\Stocks;
use App\Entity\TypeStocks;
use App\Form\StocksType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StockController
 * @package App\Controller
 * @Route("/stock")
 */
class StockController extends BasicController
{
    /**
     * @Route("", name="articles_index", methods={"GET"})
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $repo_stocks = $this->getDoctrine()->getRepository(Stocks::class);
        $repo_typeStocks = $this->getDoctrine()->getRepository(TypeStocks::class);

        $typeDraft = $repo_typeStocks->returnType('Fût');
        $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');
        $article = $repo_stocks->findBy(['type' => $typeArticle]);

        $this->data['drafts'] = $drafts;
        $this->data['bottles'] = $bottles;
        $this->data['article'] = $article;

        return $this->render("stock/index.html.twig", $this->data);
    }

    /**
     * @Route("/new", name="articles_new", methods={"GET","POST"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function new(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        if (in_array("stockmarket", $this->data['activeModes'], true)) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('articles_index');
        }

        $article = new Stocks();
        $form = $this->createForm(StocksType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('info', 'Un nouvel article a été ajouté.');

            return $this->redirectToRoute('articles_index');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'new_article';
        return $this->render(
            'stock/article.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/{id}/edit", name="articles_edit", requirements={"id"="\d+"}, methods={"GET","POST"})
     * @param Request $request
     * @param $article
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, $article)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        if (in_array("stockmarket", $this->data['activeModes'], true)) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('articles_index');
        }

//        $article = $this->getDoctrine()->getRepository(Stocks::class)->find($id);
        $form = $this->createForm(StocksType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            $this->addFlash('info', "l'article {$article->getName()} ({$article->getType()->getName()}) a bien été modifié.");

            return $this->redirectToRoute('articles_index');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'modify_article';
        $this->data['nom'] = $article->getName();
        $this->data['type'] = $article->getType()->getName();
        return $this->render(
            'stock/article.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/{id}/delete", name="articles_delete", methods={"GET"})
     * @param Stocks $article
     * @return RedirectResponse
     */
    public function delete(Stocks $article): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->getModes();

        if (in_array("stockmarket", $this->data['activeModes'], true)) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('articles_index');
        }

        $em = $this->getDoctrine()->getManager();
        $transactions = $em->getRepository(DetailsTransactions::class)->findBy(['article' => $article]);

        if (isset($transactions)) {
            foreach ($transactions as $detail) {
                $em->remove($detail);
            }
        }

        $sm_data = $em->getRepository(StockMarketData::class)->find($article->getId());
        if ($sm_data !== null) {
            $em->remove($sm_data);
        }

        $em->remove($article);
        $em->flush();
        
        $this->addFlash(
            'info', "l'article " . $article->getName() . " a bien été supprimé."
        );

        return $this->redirectToRoute('articles_index');
    }
}
