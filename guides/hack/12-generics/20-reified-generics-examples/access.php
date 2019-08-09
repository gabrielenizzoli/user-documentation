<?hh

namespace Hack\UserDocumentation\Generics\ReifiedGenerics\Examples\Access;

class C {
  const string class_const = "hi";
  public static string $class_propery = "hi";
  public static function h<reify T>(): void {}
}

// Without reified generics
function f<T as C>(classname<T> $x): void {
  $x::class_const;
  $x::$class_propery;
  $x::h<int>();
}

// With reified generics
function g<reify T as C>(): void {
  T::class_const;
  T::$class_propery;
  T::h<int>();
}
