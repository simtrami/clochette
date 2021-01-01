<?php

namespace App\Controller;

use App\Entity\TypeStocks;
use App\Form\TypeStocksType;
use App\Repository\TypeStocksRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TypeStocksController
 * @package App\Controller
 * @Route("/type-stocks")
 */
class TypeStocksController extends BasicController
{
    /**
     * @Route("", name="type_stocks_index", methods={"GET"})
     * @param TypeStocksRepository $typeStocksRepository
     * @return Response
     */
    public function index(TypeStocksRepository $typeStocksRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $this->data['type_stocks'] = $typeStocksRepository->findAll();
        return $this->render('type_stocks/index.html.twig', $this->data);
    }

    /**
     * @Route("/new", name="type_stocks_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $typeStock = new TypeStocks();
        $form = $this->createForm(TypeStocksType::class, $typeStock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($typeStock);
            $entityManager->flush();

            return $this->redirectToRoute('type_stocks_index');
        }

        $this->data['type_stock'] = $typeStock;
        $this->data['form'] = $form->createView();
        return $this->render('type_stocks/new.html.twig', $this->data);
    }

    /**
     * @Route("/{id}", name="type_stocks_show", methods={"GET"})
     * @param TypeStocks $typeStock
     * @return Response
     */
    public function show(TypeStocks $typeStock): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $this->data['type_stock'] = $typeStock;
        return $this->render('type_stocks/show.html.twig', $this->data);
    }

    /**
     * @Route("/{id}/edit", name="type_stocks_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeStocks $typeStock
     * @return Response
     */
    public function edit(Request $request, TypeStocks $typeStock): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->getModes();

        $form = $this->createForm(TypeStocksType::class, $typeStock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('type_stocks_index');
        }

        $this->data['type_stock'] = $typeStock;
        $this->data['form'] = $form->createView();
        return $this->render('type_stocks/edit.html.twig', $this->data);
    }

    /**
     * @Route("/{id}", name="type_stocks_delete", methods={"DELETE"})
     * @param Request $request
     * @param TypeStocks $typeStock
     * @return Response
     */
    public function delete(Request $request, TypeStocks $typeStock): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if ($this->isCsrfTokenValid('delete' . $typeStock->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($typeStock);
            $entityManager->flush();
        }

        return $this->redirectToRoute('type_stocks_index');
    }
}
