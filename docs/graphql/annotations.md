---
description: >-
  On the previous guide you saw how to map resolvers (callables) from a existing
  SDL (.graphql or .gql). Annotations provides the other way around, it provides
  a GraphQL SDL from annotated PHP code.
---

# @Annotations

## Thank you Doctrine

Siler's GraphQL Annotations uses the super-powers from Doctrine's Annotations and as any other dependency, is a peer that we should explicitly require:

```
$ composer require doctrine/annotations
```

## What is available:

There are 9 annotations fulfilling the GraphQL's ecosystem:

Class annotations are:

* ObjectType
* InterfaceType
* InputType
* EnumType
* UnionType
* Directive

Complementary method and property annotations are:

* Field
* Args
* EnumVal

They follow a ubiquitous language to GraphQL spec, so if you know GraphQL, there is nothing new here, you probably already know what each of them does just by its name.

