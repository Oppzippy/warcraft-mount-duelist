<script id="error-message-template" type="text/x-handlebars-template">
    <div id="error-message" class="error-message">
        <i class="material-icons error-message-close" id="error-message-close">close</i>
        {{#each this}}
            {{ this }}<br>
        {{/each}}
    </div>
</script>
