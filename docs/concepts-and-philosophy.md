# Concepts & Philosophy

## Classes, OOP, MVC - and where is the Controller?

A random guy on Reddit had this reaction after I published Siler:

> Functions everywhere, files used as methods, directories used as classes. I guess if someone wanted to see what a REST API would look like in PHP4, that's the answer.

After I questioned him why files and functions are so bad, he responded:

> It's hard to sum up in a short comment 5+ decades of industry evolution in structured and object oriented programming, versus just throwing everything in global space and piecing logic together through files. I suppose if I said things like polymorphism, dependency injection, abstraction, composition, it wouldn't mean much to you. This is why I just said it uses an obsolete PHP4 age approach. Whoever doesn't mind that, I hope they enjoy this framework.

### What decades of the OOP mantra has produced

It seems that people today \(in the PHP community, at least\) are just using classes and trying to apply OOP concepts without even realizing why they are doing it in the first place. They just hear somewhere that you **must** code using OO then avoid anything different like the plague. They don't know why or what is a class, but using one makes their code more OO-ish.

_So, not knowing why he is using it, he probably doesn't know how to respond why I shouldn't, and appealed to the bandwagon fallacy ("decades of industry")._

Disclaimer: I do not hate OOP. I just realized that like any other thing, OOP is a tool, and every tool you put in your stack must be added wisely.

### HTTP handlers do not necessarily benefit from OO

Take a closer look at this thing called a **Controller** from 99.9% of PHP frameworks. At the end of the day, they just behave like a group of functions \(named **actions**\) that are wrapped up inside of a class and called once in a request-response life cycle. They aren't doing any OO thing **at all**.  
They act like chunks of imperative code, just like a **file or function** does in Siler. There is no encapsulation or message-passing going on; there is no OO.

### When is OO a good fit?

IMHO, OO is excellent for domain modeling. When some behaviors are very deep intrinsic to its context, then I can think of no better way than to use a **Type** abstraction for it. Even a purely functional language like Haskell has a Type Class concept that implements definitions that can be properly understood as methods allowing polymorphism throught overloading.

It is pretty common, and it is easy to model and reason about a behavior/verb being attached to a type/substantive. This makes OO a very useful tool for modeling domains, because we see things this way. But it isn't how everything works, so OO isn't always the best approach.

### What is the right tool for the job?

We should keep things simple. Don't use a tool just to tell everyone that you are using it. Classes don't make your code more OO-ish and Controller-classes as HTTP entry points have no benefit from OO. People \(frameworks\) do it this way because Composer does not yet autoload functions \(I hope it will\), and because they want to accomplish MVC architecture--which is another tool that does not have any benefit in a unidirectional layer such HTTP. It's concepts were wildly spread and adopted, but it was designed for user interface programming where a Controller can properly listen for user interactions like mouse movements and Views can subscribe to Models.

### References

To finish, here are some related talks and articles:

* [Functional programming design patterns - Scott Wlaschin](https://www.youtube.com/watch?v=E8I19uA-wGY)
* [Object-Oriented Programming is Bad - Brian Will](https://www.youtube.com/watch?v=QM1iUe6IofM)
* [Stop Writing Classes - Jack Diederich](https://www.youtube.com/watch?v=o9pEzgHorH0)
* [Advanced OOP in Elixir - Wojtek Mach](https://www.youtube.com/watch?v=5EtV2JUU0Z4) _\(spoiler - is an ironic talk\)_
* [Was object-oriented programming a failure? - Wouter van Oortmerssen](https://www.quora.com/Was-object-oriented-programming-a-failure/answer/Wouter-van-Oortmerssen)
