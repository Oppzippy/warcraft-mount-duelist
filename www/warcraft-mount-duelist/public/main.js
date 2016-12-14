var realm_cache = {};

var createErrorMessage = Handlebars.compile($('#error-message-template').html());
var createRealmSelect = Handlebars.compile($('#realm-select-template').html());
var createMountList = Handlebars.compile($('#mount-list-template').html());

function displayError(errors) {
    $('#error-message').remove();
    $('#compare').append(createErrorMessage(errors));
    $('#error-message-close').click(function() {
        console.log('test');
        $(this).parent().remove();
    });
}

function populateRealms(charNum) {
    var region = $('#char' + charNum + '-region').val();
    var select = $('#char' + charNum + '-realm');
    select.empty();
    
    if (realm_cache[region]) {
        select.html(createRealmSelect(realm_cache[region]));
    } else {
        $.get("/realms", {
            region: region
        }).done(function(data) {
            realm_cache[region] = data;
            select.html(createRealmSelect(data));
        });
    }
}

function compareCharacters() {
    $.get("/compare", {
        char1_region: $('#char1-region').val(),
        char1_realm:  $('#char1-realm').val(),
        char1_name:   $('#char1-name').val(),
        char2_region: $('#char2-region').val(),
        char2_realm:  $('#char2-realm').val(),
        char2_name:   $('#char2-name').val(),
    }).done(function(data) {
        $("#compare-btn").attr("disabled", false);
        $("#compare-btn").text("Compare");
        if (data.error) {
            displayError(data.error);
        } else {
            $('#char1-mounts').html(createMountList(data.char1));
            $('#char2-mounts').html(createMountList(data.char2));
        }
    });
}

$(document).ready(function() {
    populateRealms(1);
    populateRealms(2)
    $('#compare-btn').click(function() {
        $(this).attr("disabled", true);
        $(this).text("Waiting for server...");
        compareCharacters();
    });
});

$('#char1-region').change(function() {
    populateRealms(1);
});

$('#char2-region').change(function() {
    populateRealms(2);
});