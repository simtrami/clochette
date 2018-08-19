<?php
// src/AppBundle/Controller/PreparerTenueController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Stocks;
use AppBundle\Entity\PreparerTenue;
use AppBundle\Form\PreparerTenueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PreparerTenueController extends Controller{

    /**
    * @Route("/preparation", name="preparation")
    **/
    public function getData(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $repo_stocks = $this->getDoctrine()->getRepository('AppBundle:Stocks');
        $repo_typeStocks = $this->getDoctrine()->getRepository('AppBundle:TypeStocks');

        $draft = $repo_typeStocks->returnType('Draft');
        $bottle = $repo_typeStocks->returnType('Bouteille');
        $article = $repo_typeStocks->returnType('Nourriture ou autre');

        $session = $request->getSession();

        $preparation = new PreparerTenue();

        $drafts = $repo_stocks->findByType($draft);
        $bottles = $repo_stocks->findByType($bottle);
        $articles = $repo_stocks->findByType($article);

        foreach ($drafts as $draft){
            $preparation->getDrafts()->add($draft);
        }
        foreach ($bottles as $bottle){
            $preparation->getBottles()->add($bottle);
        }
        foreach ($articles as $article){
            $preparation->getArticles()->add($article);
        }

        $form = $this->createForm(PreparerTenueType::class, $preparation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            foreach ($drafts as $draft) {
                $em->persist($draft);
            }
            foreach ($bottles as $bottle) {
                $em->persist($bottle);
            }
            foreach ($articles as $article) {
                $em->persist($article);
            }
            $em->flush();

            $session->getFlashbag()->add('info', 'La liste des articles en vente a bien été mise à jour.');

            return $this->redirectToRoute('purchase');
        }

        $data['form'] = $form->createView();

        return $this->render("preparation/preparation.html.twig", $data);
    }
}
