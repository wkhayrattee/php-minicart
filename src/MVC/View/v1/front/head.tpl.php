<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title><?php if(isset($this->title)) echo $this->title?></title>

    <meta name="description" content="<?php if(isset($this->description)) echo $this->description?>">
    <meta name="author" content="Wasseem">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <?php echo $this->container['scriptHandler']->getCssString() ?>

    <?php echo $this->container['scriptHandler']->getScriptString(true) ?>

</head>
<body class="bg-light">
