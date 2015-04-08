<?php


namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Entity\Person;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DocumentController extends Controller
{

    /**
     * @Route("/documents/")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $personId = $request->get('person');
        $em = $this->getDoctrine()->getManager();
        if ($personId) {
            $person = $em->getRepository('AppBundle:Person')->find($personId);
            if (! $person) {
                throw new NotFoundHttpException();
            }
        } else {
            $person = null;
        }
        $documentRepository = $em->getRepository('AppBundle:Document');
        if (! $personId) {
            $documents = $documentRepository->findAll();
        } else {
            $documents = $documentRepository->findBy(['author' => $person]);
        }

        $topAuthors = $documentRepository->findTopAuthors(3);

        return ['documents' => $documents, 'person' => $person, 'topAuthors' => $topAuthors];
    }

    /**
     * @Route("/documents/new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $document = new Document();
        $form = $this->createForm('document', $document);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($document);
                $em->flush();
            }

            return $this->redirectToRoute('app_document_index');
        }

        return ['form' => $form->createView()];

    }
}
