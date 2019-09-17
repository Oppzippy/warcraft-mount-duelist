<?= $this->fetch('handlebars/error-message-template.php') ?>
<?= $this->fetch('handlebars/realm-select-template.php') ?>
<?= $this->fetch('handlebars/mount-list-template.php') ?>

<div class="container char-container">
    <div class="compare" id="compare">
        <button id="compare-btn" class="button-primary">Compare</button>
    </div>
    <div id="char1" class="char-info">
        <h2>Character 1</h2>
        <?= $this->fetch('partials/character-select.php', [
            'regions' => $regions,
            'num' => 1,
        ]) ?>
    </div>
    <div id="char2" class="char-info">
        <h2>Character 2</h2>
        <?= $this->fetch('partials/character-select.php', [
            'regions' => $regions,
            'num' => 2,
        ]) ?>
    </div>
    <div class="mounts">
        <div id="char1-mounts"></div>
    </div>
    <div class="mounts">
        <div id="char2-mounts"></div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.min.js"></script>
<script type="text/javascript" src="//wow.zamimg.com/widgets/power.js"></script><script>var wowhead_tooltips = { "colorlinks": true, "iconizelinks": true, "renamelinks": true }</script>
<script src="/main.js"></script>
