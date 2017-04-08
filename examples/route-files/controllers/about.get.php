<?php

use RedBeanPHP\R;

use function Siler\Twig\render;
use function Siler\Http\Response\html;

$contacts = R::findAll('contact');

html(render('about.twig', compact('contacts')));
