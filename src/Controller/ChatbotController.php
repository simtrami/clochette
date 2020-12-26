<?php

namespace App\Controller;

use App\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends Controller
{
    protected $chatbotApiKey, $chatbotApiId;

    public function __construct($chatbotApiKey, $chatbotApiId)
    {
        $this->chatbotApiKey = $chatbotApiKey;
        $this->chatbotApiId = $chatbotApiId;
    }

    /**
     * @Route("/api/chatbot", name="chatbot-api")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function indexAction(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            if ($request->query->has('key') && $request->query->has('id')) {
                if ($request->query->get('key') !== $this->chatbotApiKey || $request->query->get('id') !== $this->chatbotApiId) {
                    return new Response(
                        'Bad id or key',
                        Response::HTTP_FORBIDDEN,
                        ['content-type' => 'text/plain']
                    );
                }
                if ($request->query->has('pseudo')) {
                    $account = $this->getDoctrine()->getRepository(Account::class)
                        ->findOneBy(['pseudo' => $request->query->get('pseudo')]);
                    if ($account != null) {
                        return $this->json(['balance' => $account->getBalance()]);
                    } else {
                        throw $this->createNotFoundException();
                    }
                } else {
                    return new Response(
                        'Missing parameter',
                        Response::HTTP_BAD_REQUEST,
                        ['content-type' => 'text/plain']
                    );
                }
            } else {
                return new Response(
                    'Wrongly formed credentials',
                    Response::HTTP_BAD_REQUEST,
                    ['content-type' => 'text/plain']
                );
            }
        } else {
            return new Response(
                'Bad method',
                Response::HTTP_METHOD_NOT_ALLOWED,
                ['content-type' => 'text/plain']
            );
        }
    }
}
