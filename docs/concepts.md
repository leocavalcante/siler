# Classes, OOP, MVC - and where is the Controller?

This is a quote from a random guy on reddit after I published Siler:

> Functions everywhere, files used as methods, directories used as classes. I guess if someone wanted to see what a REST API would look like in PHP4, that's the answer.

That was his response after I questioned him why files and functions are bad:

> It's hard to sum up in a short comment 5+ decades of industry evolution in structured and object oriented programming, versus just throwing everything in global space and piecing logic together through files. I suppose if I said things like polymorphism, dependency injection, abstraction, composition, it wouldn't mean much to you. This is why I just said it uses an obsolete PHP4 age approach. Whoever doesn't mind that, I hope they enjoy this framework.

### Maybe a reflect from decades of inside-the-box OOP mantra

It seams that people today (in PHP community, at least) are just using classes and trying to apply OOP concepts without even realize why they are doing it in first place. They just hear somewhere that you **must** code using OO them avoids anything different like a plague. Don't know why or what is a class, but using one makes they code more OO-ish.

*So, not knowing why he are using it, he probably don't know how to respond why I shouldn't and called for a fallacy as "decades of industry"*.

Before going further, I'd like to disclaim that I dot not hate OOP. I just realized that like any other thing OOP is a tool and every tool you put in your stack must be done wisely.

### HTTP entry points does not benefit from OO, at all

Take a closer look at this thing called **Controller** from 99.9% of PHP frameworks. In the end of the day, they just behave like a group of functions (named **actions**) masked under a class and called once in a request-response life cycle. They aren't doing any OO thing **at all**.<br>They act like chunks of imperative code, just like a **file or function** does in Siler. There is no encapsulation or message-passing going on, there is no OO.

### When I think OO is a good fit

IMHO, OO is excellent for domain modeling. Some behaviors are very deep intrinsic to its context, them this rules will not have a better place than a **Type** abstraction for it. Even a purely functional language like Haskell has a Type Class concept that implements definitions that can be properly understood as methods allowing polymorphism thought overloading.

It is pretty common and easy to model and understand a behavior/verb being attached to a type/substantive, this makes OO a very useful tool do model a domain, 'cause we see things this way, but it isn't how everything works, OO isn't a bullet-proof concept.

### The right tool for the right job

We should keep things simple. Don't use a tool just to tell everyone that you are using it, classes don't make your code more OO-ish and Controller-classes as HTTP entry points have no benefit from OO, people (frameworks) just use it because Composer does not autoload functions (yet, I hope) and they want to accomplish MVC architecture that is another tool that does not have any benefit in a unidirectional layer such HTTP. Its concepts were wildly spread and adopted, but its was designed for user interface programming where a Controller can properly listen user interaction like mouse movements and Views can subscribe to Models.

To finish, I would like to leave some reference links, but do not bother with the click-bait titles.

* [Functional programming design patterns - Scott Wlaschin](https://www.youtube.com/watch?v=E8I19uA-wGY)
* [Object-Oriented Programming is Bad - Brian Will](https://www.youtube.com/watch?v=QM1iUe6IofM)
* [Stop Writing Classes - Jack Diederich](https://www.youtube.com/watch?v=o9pEzgHorH0)
* [Advanced OOP in Elixir - Wojtek Mach](https://www.youtube.com/watch?v=5EtV2JUU0Z4) *(spoiler - is an ironic talk)*
* [Was object-oriented programming a failure? - Wouter van Oortmerssen](https://www.quora.com/Was-object-oriented-programming-a-failure/answer/Wouter-van-Oortmerssen)

Thank you.
