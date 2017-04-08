<?php

use RedBeanPHP\R;
use function Siler\Http\Request\post;
use function Siler\Http\Response\redirect;

$name = strip_tags(post('name'));
$message = strip_tags(post('message'));

$contact = R::dispense('contact');
$contact->name = $name;
$contact->message = $message;
$contact->date = new DateTime();

R::store($contact);

redirect('/about');
