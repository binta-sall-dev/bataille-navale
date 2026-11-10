<?php
require_once __DIR__ . '/Navire.php';

class Requin extends Navire
{
    public function __construct()
    {
        $this->nom        = 'Requin';
        $this->taille     = 3;
        $this->couleur    = '#27ae60';
        $this->touches    = 0;
        $this->place      = false;
        $this->tampon     = 1;
        $this->positions  = [];
        $this->zoneTampon = [];
    }
}