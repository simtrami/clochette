<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use AppBundle\Form\StocksType;
use AppBundle\Entity\Stocks;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockController extends Controller {

    /**
    * @Route("/stock", name="stock")
    **/
    public function showIndex(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $em = $this->getDoctrine()->getManager();
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $typeDraft = $repo_typeStocks->returnType('draft');
        $typeBottle = $repo_typeStocks->returnType('Bouteille');
        $typeArticle = $repo_typeStocks->returnType('article');

        /* les variables $typeDraft,... sont des instances de TypeStocks, 
        et non des tableaux contenant des instances de TypeStocks, 
        ceci grâce à returnType() */

        $drafts = $repo_stocks->findByType($typeDraft);
        $bottles = $repo_stocks->findByType($typeBottle);
        $article = $repo_stocks->findByType($typeArticle);

        $data=[];
        $data['drafts'] = $drafts;
        $data['bottles'] = $bottles;
        $data['article'] = $article;


        return $this->render("stock/index.html.twig", $data);
    }

    /**
     * @Route("/stock/modifier/{id_article}", name="modif_article")
     */
    public function modifArticleAction(Request $request, $id_article){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        // 1) Récupérer l'Article et construire le form
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        $article = $repo_stocks->find($id_article);
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

            $request->getSession()->getFlashbag()->add('info', 'l\'article ' .$article->getNom(). ' ('.$article->getType()->getName(). ') a bien été modifié.');

            return $this->redirectToRoute('stock');
        }

        return $this->render(
            'stock/article.html.twig',
             array(
                 'form' => $form->createView(),
                 'mode' => 'modify_article',
                 'nom' => $article->getNom(),
                 'type' => $type,
             )
        );
    }
    
    /**
     * @Route("/stock/ajout", name="ajout_article")
     */
    public function ajoutArticleAction(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        // 1) Construire le form
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

            $request->getSession()->getFlashbag()->add('info', 'Un nouvel article a été ajouté.');

            return $this->redirectToRoute('stock');
        }

        return $this->render(
            'stock/article.html.twig',
             array(
                 'form' => $form->createView(),
                 'mode' => 'new_article',
             )
        );
    }

    /**
    * @Route("/stock/supprimer", name="suppr_article")
    **/
    public function supprArticleAction(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
      
        $idarticle = $request->query->get('idarticle');

        $em = $this->getDoctrine()->getEntityManager();
        $article = $em->getRepository('AppBundle:Stocks')->find($idarticle);
        
        if (!$article) {
            throw $this->createNotFoundException("Article non trouvé pour l'id ".$idarticle);
        }

        $em->remove($article);
        $em->flush();
    
        return $this->redirectToRoute('stock');
    }
}