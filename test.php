<?php

class get
{
    public $array = [1,2];


    public function &get()
    {
        $var = &$this->array;
        return $var;
    }

}
$object = new get();
$copy = &$object->get();
$copy[] = 3;
print_r($object->array);
