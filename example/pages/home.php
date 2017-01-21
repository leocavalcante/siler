<?php

// middlewares...

// middlewares...

// middlewares...

/*
whatever middlewares you want
this is just a file, an entry point
you dont need to unit test it
so you dont need to invert its control, no need for dependency injection

instantiate and use your well-tested middlewares and services!
*/

$message = 'It works';

// you can use your favorite template engine
// echo Siler\Twig\render('home.twig', compact('message'));
// ^ Siler comes with a helper for Twig
?>
<h1><?= $message ?></h1>
<p>or just output phtml here!</p>
