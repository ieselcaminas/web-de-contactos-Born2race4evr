<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Contacto;
use App\Entity\Provincia;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ContactoFormType as ContactoType;
use Symfony\Component\HttpFoundation\Request;

final class ContactoController extends AbstractController
{
    private $contactos = [
        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],
        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],
        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],
        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],
        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]
    ];
    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request) {
        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData();
            
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }
    #[Route('/contacto/insertar', name: 'insertar_contacto')]
    public function insertar(ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        foreach($this->contactos as $c){
            $contacto = new Contacto();
            $contacto->setNombre($c["nombre"]);
            $contacto->setTelefono($c["telefono"]);
            $contacto->setEmail($c["email"]);
            $entityManager->persist($contacto);
        }

        try
        {
            $entityManager->flush();
            return new Response("Contactos Insertados");
        } catch (\Exception $e) {
            return new Response("Error insertando objetos");
        }
    }

        #[Route('/contacto/update/{id}/{nombre}', name: 'modificar_contacto')]
    public function update(ManagerRegistry $doctrine, $id, $nombre): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);
        if($contacto){
            $contacto->setNombre($nombre);
            try
            {
                $entityManager->flush();
                return $this->render('ficha_contacto.html.twig', [
                    'contacto' => $contacto
                ]);
            } catch (\Exception $e) {
                return new Response("Error insertando objetos");
            }

        }else
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
    }

    #[Route('/contacto/delete/{id}/{nombre}', name: 'eliminar_contacto')]
    public function delete(ManagerRegistry $doctrine, $id): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($id);
        if($contacto){
            try
            {
                $entityManager->remove($contacto);
                $entityManager->flush();
                return new Response("Contacto eliminado");

            } catch (\Exception $e) {
                return new Response("Error eliminando objeto");
            }

        }else
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => null
            ]);
    }

    #[Route('/contacto/insertarConProvincia', name: 'insertar_con_provincia_contacto')]
    public function insertarConProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine->getManager();
        $provincia = new Provincia();

        $provincia->setNombre("Inserción de prueba con provincia");
        $contacto = new Contacto();

        $contacto->setNombre("Inserción de prueba con provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("insercion.de.prueba.provincia@contacto.es");
        $contacto->setProvincia($provincia);

        $entityManager->persist($provincia);
        $entityManager->persist($contacto);
        
        $entityManager->flush();
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }

    #[Route('/contacto/insertarSinProvincia', name: 'insertar_sin_provincia_contacto')]
    public function insertarSinProvincia(ManagerRegistry $doctrine): Response{
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $provincia = $repositorio->findOneBy(['nombre'=> 'Alicante']);
        
        $contacto = new Contacto();

        $contacto->setNombre("Inserción de prueba sin provincia");
        $contacto->setTelefono("900220022");
        $contacto->setEmail("insercion.de.prueba.sin.provincia@contacto.es");
        $contacto->setProvincia($provincia);

        $entityManager->persist($contacto);
        
        $entityManager->flush();
        return $this->render('ficha_contacto.html.twig', [
            'contacto' => $contacto
        ]);
    }

    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements:["codigo"=>"\d+"])]
    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo) {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);
        if ($contacto){
            $formulario = $this->createForm(ContactoType::class, $contacto);
            $formulario->handleRequest($request);
            if ($formulario->isSubmitted() && $formulario->isValid()) {
                $contacto = $formulario->getData();
                $entityManager = $doctrine->getManager();
                $entityManager->persist($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('ficha_contacto', ["codigo" => $contacto->getId()]);
            }
            return $this->render('nuevo.html.twig', array(
                'formulario' => $formulario->createView()
            ));
        }else{
            return $this->render('ficha_contacto.html.twig', [
                'contacto' => NULL
            ]);
        }
    }

    #[Route('/contacto/{codigo?1}', name: 'ficha_contacto')]
    public function ficha(ManagerRegistry $doctrine, $codigo): Response{
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render('ficha_contacto.html.twig', [
        'contacto' => $contacto
        ]);
    }
}