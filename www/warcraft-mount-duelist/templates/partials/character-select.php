<select id="char<?= $num ?>-region" class="u-full-width">
    <?php foreach($regions as $region => $realms): ?>
        <option value="<?= $region ?>"><?= strtoupper($region) ?></option>
    <?php endforeach; ?>
</select>

<!-- Realms will be populated using AJAX -->
<select id="char<?= $num ?>-realm" class="u-full-width"></select>

<input id="char<?= $num ?>-name" type="text" placeholder="Character Name" class="u-full-width"></input>