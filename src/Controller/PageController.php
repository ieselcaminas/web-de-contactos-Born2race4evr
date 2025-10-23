<?php

namespace App\Controller;

use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\ContactoFormType as ContactoType;

final class PageController extends AbstractController
{
    #[Route('/', name: 'inicio')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contactos = $repositorio->findAll();

        return $this->render('inicio.html.twig', [
            'contactos' => $contactos
        ]);
        
    }
    #[Route('/contacto/{codigo?1}', name: 'ficha')]
    public function ficha(ManagerRegistry $doctrine, Request $request, $codigo = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // Comprobar login

        $em = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);

        // Si no hay código, mostramos la lista de contactos
        if ($codigo === null) {
            $contactos = $repositorio->findAll();
            return $this->render('inicio.html.twig', [
                'contactos' => $contactos
            ]);
        }

        // Si hay código, mostramos la ficha del contacto
        $contacto = $repositorio->find($codigo);
        if (!$contacto) {
            throw $this->createNotFoundException("Contacto $codigo no encontrado");
        }

        $form = $this->createForm(ContactoType::class, $contacto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('guardar')->isClicked()) {
                $em->flush();
            } elseif ($form->get('borrar')->isClicked()) {
                $em->remove($contacto);
                $em->flush();
                return $this->redirectToRoute('inicio');
            }
        }

        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto,
            'form' => $form->createView()
        ]);
    }
}