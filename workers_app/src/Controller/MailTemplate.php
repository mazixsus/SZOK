<?php
/**
 * Created by PhpStorm.
 * User: Piotr
 * Date: 04.12.2018
 * Time: 18:09
 */

namespace App\Controller;


use App\Entity\Uzytkownicy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MailTemplate extends AbstractController
{
    /**
     * @Route("/sendMail",
     *      name="workers_app/sendMail")
     */
//wchodząc w ten link wysyłasz maila..
    public function sendEmail( \Swift_Mailer $mailer)
    {
        //https://swiftmailer.symfony.com/docs/messages.html <- źródło

//        $html = $this->renderView('workersApp/ticket/ticket.html.twig', ['bilety' => $tickets]);      //fragment generowania mateuszowego biletu
//        $data = $snappy->getOutputFromHtml($html);                                                    //zapisanie wygenerowanego pliku do zmiennej
//        $attachment = new Swift_Attachment($data, 'bilety.pdf', 'application/pdf');                   //tworzenie załaczona o wybranej nazwie o wybranym fomracie
        $client = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find(rand(1,50));
        $message = (new \Swift_Message());          //nowa wiadomosć
        $message ->setSubject('Temat');       //temet wiadomości
        $message ->setFrom('szok.smtp@gmail.com');  //nadawca
        $message ->setTo('rksmazur@gmail.com');       //odbiorca
        $message ->setBody($this->renderView(       //treść maila jako twig
            'workersApp/mail/mailRegistration.html.twig',
            array('client' => $client)      //przekazywanie wartości do twinga, jakby ktoś nie wiedział
        ),
            'text/html'                             //typ
        );
       // $message ->setBody("mail!");                   //treść maila wpisana ręcznie, mozna wpisać kod html
       // $message ->attach(Swift_Attachment::fromPath('../../images/no_poster.jpg')->setFilename('cool.jpg'));   //dodanie załącznika i zmiana nazwy pliku
        //$message ->attach(Swift_Attachment::fromPath('../../images/no_poster2.jpg')->setFilename('cool2.jpg')); //można dodać więcej załaczników, tylko nie mogą one mieć tej samej nazwy
                                                                                                                //jezeli nazwa pliku nie zostanie zmieniona wtedy idzie taka jak źródłowa
//        $message->attach($attachment);                  //dodanie wcześniej wygenerowanego mateuszowego biletu.

        $mailer->send($message);

        return new Response(
            '<html><body>Mail wysłany </body></html>'
        );
    }

    public function sendEmail2( \Swift_Mailer $mailer)
    {
        //to samo ale inaczej zbudowane.
        $client = $this->getDoctrine()->getRepository(Uzytkownicy::class)->find(rand(1,50));
        $message = (new \Swift_Message('Hello Email'))  //temat
            ->setFrom('szok.smtp@gmail.com')               //nadawaca
            ->setTo('g.nowak126@gmail.com')            //odbiorca
            ->setBody(                                  //wiadomość
                $this->renderView('workersApp/mail/mailRegistration.html.twig',
                    array('client' => $client)
                ),
                'text/html'
            )
            ->attach(Swift_Attachment::fromPath('../../images/no_poster.jpg')->setFilename('cool.jpg')); //załacznik
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        $mailer->send($message);

        return new Response(
            '<html><body>Mail wysłany </body></html>'
        );
    }
}