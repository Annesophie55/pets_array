<?php

class Animal {
    private $idAnimal;
    private $nom;
    private $age;
    private $sexe;
    private $libelle;
    private $url;
    public static $mesAnimaux = [];
    public static $types = [];
    public static $images = [];

    public function __construct($idAnimal, $nom, $age, $sexe, $libelle, $url)
    {
        $this->idAnimal = $idAnimal;
        $this->nom = $nom;
        $this->age = $age;
        $this->sexe = $sexe;
        $this->libelle = $libelle;
        $this->url = $url;
    }

    public function getId(){
        return $this->idAnimal; 
    }

    public function getName(){
        return $this->nom; 
    }

    public function setName($nom){
        return $this->nom = $nom;
    }

    public function getType() {
        return $this->libelle ?? "";
    }

    public function getImage() {
        return $this->url ?? "";
    }

    public function setImage($url) {
        return $this->url = $url;
    }

    public function getAge() {
        return $this->age;
    }

    public function getSexe() {
        return $this->sexe;
    }
}

class MyPDO {
    public $servername;
    public $username;
    public $char;
    public $password;
    public $dbname;
    public $conn;

    public function __construct($servername, $dbname, $char, $username, $password)
    {
        $this->servername = $servername;
        $this->dbname = $dbname;
        $this->char = $char;
        $this->username = $username; 
        $this->password = $password;
    }

    public function recupererBDD(){
        try {
            $this->conn = new PDO('mysql:host=' . $this->servername . ';dbname=' . $this->dbname . ';charset=' . $this->char,
            $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function close() {
        $this->conn = null;
    }
}

$db = new MyPDO("localhost","animaux", "utf8", "root", "");
$db->recupererBDD();

class AnimalDAO {

    public static $types = [];

    public static function recupererAnimaux() {
        $db = new MyPDO("localhost", "animaux", "utf8", "root", "");
        $db->recupererBDD();
        $req = $db->conn->prepare("SELECT a.idAnimal, a.nom, a.age, a.sexe, t.libelle, i.url
                                  FROM animal a
                                  LEFT JOIN type t ON a.idType = t.idType
                                  LEFT JOIN image_animal ia ON a.idAnimal = ia.idAnimal
                                  LEFT JOIN image i ON ia.idImage = i.idImage");
        $req->execute();

        $mesAnimaux = $req->fetchAll(PDO::FETCH_ASSOC);
        Animal::$mesAnimaux = []; 

foreach ($mesAnimaux as $animalData) {
    $idAnimal = $animalData['idAnimal'];

    if (isset(Animal::$mesAnimaux[$idAnimal])) {
        $animalInstance = Animal::$mesAnimaux[$idAnimal];
    } else {
        $nom = $animalData['nom'];
        $age = $animalData['age'];
        $sexe = $animalData['sexe'];
        $libelle = $animalData['libelle'];
        $url = $animalData['url'];

        $animalInstance = new Animal($idAnimal, $nom, $age, $sexe, $libelle, $url);
        Animal::$mesAnimaux[$idAnimal] = $animalInstance;
    }

    $urls = AnimalDAO::recupererImage($idAnimal);
    foreach ($urls as $url) {
        $animalInstance->setImage($url);
    }
}

    }

    public static function recupererType($idAnimal) {
        $db = new MyPDO("localhost", "animaux", "utf8", "root", "");
        $db->recupererBDD();

        $req = $db->conn->prepare("SELECT libelle FROM type WHERE idType = (SELECT idType FROM animal WHERE idAnimal = :idAnimal)");
        $req->bindParam(':idAnimal', $idAnimal);
        $req->execute();

        $libelle = $req->fetchColumn();

        return $libelle;
    }

    public static function recupererImage($idAnimal) {
        $db = new MyPDO("localhost", "animaux", "utf8", "root", "");
        $db->recupererBDD();

        $req = $db->conn->prepare("SELECT image.url FROM image_animal JOIN image ON image_animal.idImage = image.idImage WHERE image_animal.idAnimal = :idAnimal");
        $req->bindParam(':idAnimal', $idAnimal);
        $req->execute();

        $urls = $req->fetchAll(PDO::FETCH_COLUMN);

        return $urls;
    }
} 

AnimalDAO::recupererAnimaux();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <title>Document</title>
</head>
<body>
   <div class="w-100 primary">
     <table class="table ">
     <thead>
     <tr class="primary">
         <th>Nom</th>
         <th>Type</th>
         <th>Age</th>
         <th>Sexe</th>
         <th>Image</th>
     </tr>
     </thead>
     <tbody>
     <?php foreach (Animal::$mesAnimaux as $animal) { ?>
         <tr>
             <td><?= $animal->getName() ?></td>
             <td><?= $animal->getType() ?></td>
             <td><?= $animal->getAge() ?></td>
             <td><?= ($animal->getSexe()==0) ? "femelle" : "male" ?></td>

         
             <td class="w-25">
             <?php
         $urls = AnimalDAO::recupererImage($animal->getId());
         foreach ($urls as $url) { ?>
                <img class="object-fit-cover border rounded w-25"  src="images/<?= $url ?>" alt="images/<?= $url ?>">
                <?php } ?>
            </td>
             
         </tr> 
    <?php } ?>
    </tbody>
         </table>
   </div>
</body>
</html>
