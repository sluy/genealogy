<?php

//Incluimos requeridos
include_once("genealogy.php");
include_once("member.php");
//Iniciamos Genealogy
$g = new Genealogy();
//Creamos todas las relaciones
$g->get("Norelis Santander")->addParents(["Florelia Santander","Isidro Garcia"]);
$g->get("Antonio Garcia")->addParents(["Florelia Santander","Isidro Garcia"]);
$g->get("Jesus Marques")->addParent("Carmen Perez");
$g->get("Florelia Santander")->addParent("Carmen Perez");
$g->get("Isidro Garcia")->addParents(["Yolanda Zambrano", "Jesus Garcia"]);
$g->get("Jesus Garcia")->addParent("Maria Herrera");
//imprimimos el arbol
$g->get("Maria Herrera")->printTree();
