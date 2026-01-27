<?php
  require_once "includes/guard.php";
  require_approved();
  require_role(["administrator", "editor"]);
?>

<section class="content">
  <h1>CMS</h1>

  <h2>Novosti</h2>
  <ul>
    <li><a href="index.php?menu=23">Popis vijesti</a></li>
    <li><a href="index.php?menu=22">Nova vijest</a></li>
  </ul>

  <h2>Korisnici</h2>
  <ul>
    <li><a href="index.php?menu=21">Upravljanje korisnicima</a></li>
  </ul>

  <h2>Katalog penjališta</h2>
  <ul>
    <li><a href="index.php?menu=30">Penjališta</a></li>
    <li><a href="index.php?menu=33">Sektori</a></li>
    <li><a href="index.php?menu=36">Smjerovi</a></li>
  </ul>
</section>
