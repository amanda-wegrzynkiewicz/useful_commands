<?php

class Car
{

    public function drive()
    {
        return "Driving a car!";
    }
}

class Bike
{
    public function ride()
    {
        return "Riding a bike!";
    }
}

function vehicleFactory($type)
{
    if ($type === 'car') {
        return new Car();
    } elseif ($type === 'bike') {
        return new Bike();
    } else {
        throw new Exception("Unknown vehicle type");
    }
}

$car = vehicleFactory('car');
echo $car->drive();

$bike = vehicleFactory('bike');
echo $bike->ride();