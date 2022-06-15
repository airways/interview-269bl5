<?php

namespace Collegeplannerpro\InterviewReport;

use Psr\Container\ContainerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


class Controller
{
    private \Twig\Environment $twig;
    private Repository $repository;

    public function __construct(ContainerInterface $container) {
        $this->twig = $container->get('twig');
        $this->repository = $container->get('repository');
        $this->mailer = $container->get('mailer');
    }

    public function home(Request $request, Response $response): Response
    {
        $response->getBody()->write($this->twig->render('home.html.twig'));
        return $response;
    }

    public function paymentsReport(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        if(!isset($params['format'])) { $params['format'] = 'html'; }

        $invoices = $this->repository->allInvoicesWithPayments();

        $context = [
            'invoices' => $invoices,
        ];

        if($params['format'] == 'json') {
            $response = $response->withHeader('Content-type', 'application/json');
            $content = json_encode($context);
        } else {
            $content = $this->twig->render('paymentsReport.html.twig', $context);
        }

        $response->getBody()->write($content);

        error_log('memory_get_peak_usage='.memory_get_peak_usage());

        return $response;
    }

    public function sendReminderEmail(Request $request, Response $response, array $args): Response
    {
        error_log('sendReminderEmail::'.print_r($args, true));

        $invoiceId = $args['invoiceId'];

        $invoice = $this->repository->invoiceDetails($invoiceId);
        $contact = $this->repository->contactDetails($invoice->contact_id);

        $mailerResult = $this->mailer->send(
            [$contact->email],
            'Reminder for invoice '. $invoiceId,
            'Please pay your past due balance on invoice '.$invoiceId.'. If you have already sent payment, please disregard this notice.');
        
        $context = [
            'error' => !$mailerResult->isSuccess,
            'message' => $mailerResult->errorMessage ?: '',
        ];

        $content = json_encode($context);
        error_log('sendReminderEmail::result='.$content);
        $response->getBody()->write($content);
        return $response;
    }
}
