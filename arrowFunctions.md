1. Prosta funkcja strzałkowa
```php
$add = fn($a, $b) => $a + $b;

echo $add(2, 3);
```
W tym przykładzie funkcja strzałkowa przyjmuje dwa argumenty $a i $b, a następnie zwraca ich sumę.

2. Użycie funkcji strzałkowej w array_map
```php
$doubled = array_map(fn($n) => $n * 2, $numbers);

print_r($doubled); //[2, 4, 6, 8, 10]
```

3. Zasięg zmiennych w funkcjach strzałkowych
Jedną z ważnych cech funkcji strzałkowych jest to, że automatycznie dziedziczą zmienne ze swojego otoczenia (z zasięgu, w którym zostały zdefiniowane) bez konieczności używania słowa kluczowego use.  

Dziedziczenie zmiennych
```php
$multiplier = 3;

$multiply = fn($n) => $n * $multiplier;

echo $multiply(5); //15
```