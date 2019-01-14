<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ModeController extends BasicController
{
    /**
     * @Route("/settings/modes", name="modes")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->getModes();
        $this->data['modes'] = $this->getDoctrine()->getRepository('AppBundle:Settings')->findBy(['type' => 'mode']);
        return $this->render('settings/modes/index.html.twig', $this->data);
    }

    /**
     * @Route("/settings/modes/toggle_mode", name="toggle_mode")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleModeAction(Request $request)
    {
        if ($request->request->get('id')) {
            $id = $request->request->get('id');
            $mode = $this->getDoctrine()->getRepository('AppBundle:Settings')->find($id);
            $parameters = $mode->getParameters();
            switch ($parameters['state']) {
                case "0":
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

        return $this->redirectToRoute('modes');
    }
}
