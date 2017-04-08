<?php

use RedBeanPHP\R;
use function Siler\Http\Response\html;
use function Siler\Twig\render;

$contacts = R::findAll('contact');

html(render('about.twig', compact('contacts')));
