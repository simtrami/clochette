<?php
// src/AppBundle/Controller/PreparationController.php
namespace AppBundle\Controller;

use AppBundle\Form\SelectArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Stocks;

class PreparationController extends Controller
{

    /**
    * @Route("/preparation", name="preparation")
    **/
    public function getData(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $conn = $this->getDoctrine()->getManager()->getConnection();

        /* futs */ $sql = ' SELECT * FROM stocks S WHERE S.type="draft" AND S.quantite > :quantite ';
        
        $drafts_notnull = $conn->prepare($sql);
        $drafts_notnull->execute(['quantite' => 0]);

        /* bouteilles */ $sql = ' SELECT * FROM stocks S WHERE S.type="bottle" AND S.quantite > :quantite ';
        
        $bottles_notnull = $conn->prepare($sql);
        $bottles_notnull->execute(['quantite' => 0]);

        /* articles */ $sql = ' SELECT * FROM stocks S WHERE S.type="article" AND S.quantite > :quantite ';
        
        $articles_notnull = $conn->prepare($sql);
        $articles_notnull->execute(['quantite' => 0]);

        $data=[];
        $data['drafts_notnull'] = $drafts_notnull;
        $data['bottles_notnull'] = $bottles_notnull;
        $data['articles_notnull'] = $articles_notnull;

        /* $stock = $repo_stocks->findAll();

        foreach ($stock as $article){
            $uniqueFormName = $article->getIdarticle(); //Use some unique data to generate the form name.
            $form = $this->get('form.factory')->createNamed($uniqueFormName, SelectArticleType::class, $article);
            $stockForms[$uniqueFormName] = $form;
        }
        foreach ($stockForms as $formName => $form){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $article = $repo_stocks->findByIdarticle($formName);
                $em->persist($article);
                $em->flush();
                
            }
            $data['stockForms'][$formName] = $form->createView();
        }
 */

        $drafts = $repo_stocks->findByType("draft");
        $bottles = $repo_stocks->findByType("bottle");
        $articles = $repo_stocks->findByType("article");

        foreach ($drafts as $draft){
            $uniqueFormName = $draft->getIdarticle(); //Use some unique data to generate the form name.
            $form = $this->get('form.factory')->createNamed($uniqueFormName, SelectArticleType::class, $draft);
            $draftForms[$uniqueFormName] = $form;
        }
        foreach ($bottles as $bottle){
            $uniqueFormName = $bottle->getIdarticle(); //Use some unique data to generate the form name.
            $form = $this->get('form.factory')->createNamed($uniqueFormName, SelectArticleType::class, $bottle);
            $bottleForms[$uniqueFormName] = $form;
        }
        foreach ($articles as $article){
            $uniqueFormName = $article->getIdarticle(); //Use some unique data to generate the form name.
            $form = $this->get('form.factory')->createNamed($uniqueFormName, SelectArticleType::class, $article);
            $articleForms[$uniqueFormName] = $form;
        }

        foreach ($draftForms as $formName => $form){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $draft = $repo_stocks->find($form->getName());
                $em->persist($draft);
                $em->flush();
                return $this->redirectToRoute('preparation');
            }
            $data['draftForms'][$formName] = $form->createView();
        }
        foreach ($bottleForms as $formName => $form){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $bottle = $repo_stocks->find($formName);
                $em->persist($bottle);
                $em->flush();
                return $this->redirectToRoute('preparation');
            }
            $data['bottleForms'][$formName] = $form->createView();
        }
        foreach ($articleForms as $formName => $form){
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $article = $repo_stocks->find($formName);
                $em->persist($article);
                $em->flush();
                return $this->redirectToRoute('preparation');
            }
            $data['articleForms'][$formName] = $form->createView();
        }

        /* if (!is_null($request->query->get("articlesForSale"))){
            $idArticlesForSale = json_decode($request->query->get("articlesForSale"));
            if (!is_null($idArticlesForSale)) {
                foreach ($idArticlesForSale as $id) {
                    $article = $em->getRepository('AppBundle:Stocks')->find($id);
                    if (is_null($article)) {
                        throw $this->createNotFoundException("Article non trouvé pour l'id ".$idarticle);
                    } else {
                        $article->setIsForSale(1);
                    }
                }
            }

        } */

        /* $idList = $request->query->get('articlesForSale');
        if (!is_null($idList)){
            $idForSale = explode(",", $idList, -1);
            foreach ($idForSale as $id){
                $article = $em->getRepository('AppBundle:Stocks')->find($id);
                if (is_null($article)) {
                    throw $this->createNotFoundException("Article non trouvé pour l'id ".$idarticle);
                } else {
                    $article->setIsForSale(1);
                }
            }   
        } */

        return $this->render("preparation/preparation.html.twig", $data);
    }
}