<?php
// src/AppBundle/Controller/StockController.php
namespace AppBundle\Controller;

use AppBundle\Form\ArticleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');

        $drafts = $repo_stocks->findByType('draft');
        $bottles = $repo_stocks->findByType('bottle');
        $article = $repo_stocks->findByType('article');

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

        // 1) Récupérer l'Article et construire le form
        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $article = $repo_stocks->find($id_article);
        // Récupération du type d'article et traduction pour l'affichage
        switch($article->getType()) {
            case "draft":
                $type = "Fût";
                break;
            case "bottle":
                $type = "Bouteille";
                break;
            case "article":
                $type = "Nourriture ou autre";
                break;
            default:
                $type = "Type non défini";
        }
        
        $form = $this->createForm(ArticleType::class, $article);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer l'Article!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            // ... autres actions

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

        // 1) Construire le form
        $article = new Stocks();
        $form = $this->createForm(ArticleType::class, $article);

        // 2) Traiter le submit (uniquement sur POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Enregistrer l'Article!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            // ... autres actions

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