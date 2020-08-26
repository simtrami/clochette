<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Stocks;
use AppBundle\Form\StocksType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends BasicController
{
    /**
    * @Route("/stock", name="stock")
    **/
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        $typeDraft = $repo_typeStocks->returnType('Fût');
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $typeArticle = $repo_typeStocks->returnType('Nourriture ou autre');

        /* les variables $typeDraft,... sont des instances de TypeStocks, 
        et non des tableaux contenant des instances de TypeStocks, 
        ceci grâce à returnType() */

        $drafts = $repo_stocks->findBy(['type' => $typeDraft]);
        $bottles = $repo_stocks->findBy(['type' => $typeBottle]);
        $article = $repo_stocks->findBy(['type' => $typeArticle]);

        $this->data['drafts'] = $drafts;
        $this->data['bottles'] = $bottles;
        $this->data['article'] = $article;


        return $this->render("stock/index.html.twig", $this->data);
    }

    /**
     * @Route("/stock/{id}/modifier", name="modif_article", requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return RedirectResponse|Response
     */
    public function modifArticleAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        // 1) Récupérer l'Article et construire le form
        if (in_array("stockmarket", $this->data['activeModes'])) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('stock');
        }

        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $article = $repo_stocks->find($id);
        $type = $article->getType()->getName();
        // Récupération du type d'article et traduction pour l'affichage
        
        $form = $this->createForm(StocksType::class, $article);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer l'Article!
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            // ... autres actions

            $this->addFlash('info', "l'article " . $article->getNom() . " (" . $article->getType()->getName() . ") a bien été modifié.");

            return $this->redirectToRoute('stock');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'modify_article';
        $this->data['nom'] = $article->getNom();
        $this->data['type'] = $type;

        return $this->render(
            'stock/article.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/stock/new", name="ajout_article")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function ajoutArticleAction(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        // 1) Construire le form
        if (in_array("stockmarket", $this->data['activeModes'])) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('stock');
        }

        $article = new Stocks();

        $form = $this->createForm(StocksType::class, $article);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer l'Article!
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            // ... autres actions

            $this->addFlash('info', 'Un nouvel article a été ajouté.');

            return $this->redirectToRoute('ajout_article');
        }

        $this->data['form'] = $form->createView();
        $this->data['form_mode'] = 'new_article';

        return $this->render(
            'stock/article.html.twig',
            $this->data
        );
    }

    /**
     * @Route("/stock/delete", name="suppr_article")
     * @param Request $request
     * @return RedirectResponse
     */
    public function supprArticleAction(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $this->getModes();

        if (in_array("stockmarket", $this->data['activeModes'])) {
            $this->addFlash('error', "Cette action n'est pas autorisée en mode Stock Market !");
            return $this->redirectToRoute('stock');
        }

        $idArticle = $request->query->get('idarticle');
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Stocks')->find($idArticle);

        if (!$article) {
            throw $this->createNotFoundException("Article non trouvé pour l'id " . $idArticle);
        }

        $transactions = $em->getRepository('AppBundle:DetailsTransactions')->findBy(['article' => $idArticle]);
        if (isset($transactions)) {
            foreach ($transactions as $elt_commande) {
                $em->remove($elt_commande);
            }
        }

        $sm_data = $em->getRepository('AppBundle:StockMarketData')->find($idArticle);
        if (isset($sm_data)) {
            $em->remove($sm_data);
        }

        $em->remove($article);

        $em->flush();
        
        $this->addFlash(
            'info', "l'article " . $article->getNom() . " a bien été supprimé."
        );

        return $this->redirectToRoute('stock');
    }
}
