<script id="mount-list-template" type="text/x-handlebars-template">
    <ol>
        {{#each this}}
            <li><a href="http://www.wowhead.com/{{id_type}}={{id}}">{{@key}}</a></li>
        {{/each}}
    </ol>
</script>