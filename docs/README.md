---
description: >-
  Siler is a set of general purpose high-level abstractions aiming an API for
  declarative programming in PHP.
---

# Siler

> Simplicity is the ultimate sophistication. – Leonardo Da Vinci

You can use it within any framework or standalone, as a micro-framework:

```bash
composer require leocavalcante/siler
```

### A Hello World example

```php
use Siler\Functional as λ;
use Siler\Route;

Route\get('/', λ\puts('Hello World'));
```

This outputs "Hello World" when the file is reached via HTTP using the GET method and an URI path that matches "/". **Got the idea, right?**

