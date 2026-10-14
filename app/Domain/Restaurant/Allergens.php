<?php
namespace App\Domain\Restaurant;
class Allergens {
 public const LABELS=['gluten'=>'Cereais com glúten','crustaceans'=>'Crustáceos','eggs'=>'Ovos','fish'=>'Peixe','peanuts'=>'Amendoins','soy'=>'Soja','milk'=>'Leite','nuts'=>'Frutos de casca rija','celery'=>'Aipo','mustard'=>'Mostarda','sesame'=>'Sésamo','sulphites'=>'Sulfitos','lupin'=>'Tremoço','molluscs'=>'Moluscos'];
 public static function labels(?array $codes):array{return array_values(array_intersect_key(self::LABELS,array_flip($codes??[])));}
}
