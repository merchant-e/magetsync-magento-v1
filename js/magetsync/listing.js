var categoryReloadUrl, categoryListingId;

function tootglePriceInput(evt) {
  var priceInput = $('price');
  if (evt.element().checked == true) {
    priceInput.disabled = false;
  } else {
    priceInput.disabled = true;
    priceInput.value = $('orig-price-val').value;
  }
}

function calculateEstimatePrice(reset) {
  var affectPriceVal = document.getElementById('affect_value');
  var priceValue = 10;
  if (reset !== true) {
    if (document.getElementById('affect_strategy').value == 'percentage') {
      var delta = priceValue * Number(affectPriceVal.value)/100;
    } else {
      var delta = Number(affectPriceVal.value);
    }
    if (document.getElementById('pricing_rule').value == 'increase') {
      priceValue += delta;
    } else {
      priceValue -= delta;
    }
  }

  document.getElementById('estimate-price').update(priceValue);
}

function togglePricing(select) {
  if (select.value != 'original') {
    document.getElementById('affect_value').show();
    document.getElementById('affect_strategy').show();
    calculateEstimatePrice();
  } else {
    document.getElementById('affect_value').hide();
    document.getElementById('affect_strategy').hide();
    calculateEstimatePrice(true);
  }
}

function getNextData(selectElement) {
  if (selectElement.value != '') {
    document.getElementById('is_supply').disabled = false;
    $('is_supply').observe('change', function(e){
      var value = document.getElementById('is_supply').value;
      if (value != null) {
        document.getElementById('when_made').disabled = false;
      } else {
        document.getElementById('when_made').value = '';
        document.getElementById('when_made').disabled = true;
      }
    });
  } else {
    document.getElementById('is_supply').value = '';
    document.getElementById('when_made').value = '';
    document.getElementById('is_supply').disabled = true;
    document.getElementById('when_made').disabled = true;
  }
}

function fillProperties(selectElement){
  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('properties_holder').update('');
      $('properties_holder').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.taxonomy != '') {
        $('properties_holder').update(parsedResponse.taxonomy);
      }
    }
  });
}

function getCategory(selectElement){

  $('subcategory_id').up(0).up(0).hide();
  $('subsubcategory_id').up(0).up(0).hide();
  $('subcategory4_id').up(0).up(0).hide();
  $('subcategory5_id').up(0).up(0).hide();
  $('subcategory6_id').up(0).up(0).hide();
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subcategory_id').update('');
    $('subsubcategory_id').update('');
    $('subcategory4_id').update('');
    $('subcategory5_id').update('');
    $('subcategory6_id').update('');
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subcategory_id').update('');
      $('subcategory_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if(parsedResponse.categories == '')
      {
        $('subcategory_id').up(0).up(0).hide();
        $('subsubcategory_id').up(0).up(0).hide();
        $('subcategory4_id').up(0).up(0).hide();
        $('subcategory5_id').up(0).up(0).hide();
        $('subcategory6_id').up(0).up(0).hide();
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subcategory_id').up(0).up(0).show();
        $('subcategory_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function getSubCategory(selectElement){
  $('subsubcategory_id').up(0).up(0).hide();
  $('subcategory4_id').up(0).up(0).hide();
  $('subcategory5_id').up(0).up(0).hide();
  $('subcategory6_id').up(0).up(0).hide();
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subsubcategory_id').update('');
    $('subcategory4_id').update('');
    $('subcategory5_id').update('');
    $('subcategory6_id').update('');
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subsubcategory_id').update('');
      $('subsubcategory_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.categories == '') {
        $('subsubcategory_id').up(0).up(0).hide();
        $('subcategory4_id').up(0).up(0).hide();
        $('subcategory5_id').up(0).up(0).hide();
        $('subcategory6_id').up(0).up(0).hide();
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subsubcategory_id').up(0).up(0).show();
        $('subsubcategory_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function getSubSubCategory(selectElement) {
  $('subcategory4_id').up(0).up(0).hide();
  $('subcategory5_id').up(0).up(0).hide();
  $('subcategory6_id').up(0).up(0).hide();
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subcategory4_id').update('');
    $('subcategory5_id').update('');
    $('subcategory6_id').update('');
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subcategory4_id').update('');
      $('subcategory4_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.categories == '') {
        $('subcategory4_id').up(0).up(0).hide();
        $('subcategory5_id').up(0).up(0).hide();
        $('subcategory6_id').up(0).up(0).hide();
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subcategory4_id').up(0).up(0).show();
        $('subcategory4_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function getSubCategory4(selectElement) {
  $('subcategory5_id').up(0).up(0).hide();
  $('subcategory6_id').up(0).up(0).hide();
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subcategory5_id').update('');
    $('subcategory6_id').update('');
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subcategory5_id').update('');
      $('subcategory5_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.categories == '') {
        $('subcategory5_id').up(0).up(0).hide();
        $('subcategory6_id').up(0).up(0).hide();
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subcategory5_id').up(0).up(0).show();
        $('subcategory5_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function getSubCategory5(selectElement){
  $('subcategory6_id').up(0).up(0).hide();
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subcategory6_id').update('');
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subcategory6_id').update('');
      $('subcategory6_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.categories == '') {
        $('subcategory6_id').up(0).up(0).hide();
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subcategory6_id').up(0).up(0).show();
        $('subcategory6_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function getSubCategory6(selectElement){
  $('subcategory7_id').up(0).up(0).hide();

  if (selectElement.value == '' || !categoryReloadUrl || !categoryListingId) {
    $('subcategory7_id').update('');

    return false;
  }
  var reloadurl = categoryReloadUrl + 'tag/' + selectElement.value + '/listing/' + categoryListingId;
  new Ajax.Request(reloadurl, {
    method: 'get',
    onLoading: function () {
      $('subcategory7_id').update('');
      $('subcategory7_id').update('Searching…');
    },
    onComplete: function(subform) {
      var parsedResponse = JSON.parse(subform.responseText);
      if (parsedResponse.categories == '') {
        $('subcategory7_id').up(0).up(0).hide();
      } else {
        $('subcategory7_id').up(0).up(0).show();
        $('subcategory7_id').update(parsedResponse.categories);
        if (parsedResponse.taxonomy != '') {
          $('properties_holder').update(parsedResponse.taxonomy);
        }
      }
    }
  });
}

function enableStyle2(selectElement){
  if (selectElement.value == '') {
    $('style_two').value = '';
    $('style_two').up(0).up(0).hide();
  } else {
    $('style_two').up(0).up(0).show();
  }
}

document.observe('dom:loaded', function() {
  categoryReloadUrl = $('category_reload_url').value;
  categoryListingId = $('category_listing_id').value;

  var $customPrice = $('custom-price');
  var $affectValue = $('affect_value');
  var $affectStrategy = $('affect_strategy');
  var $pricingRule = $('pricing_rule');
  $customPrice ? $customPrice.observe('change', tootglePriceInput) : '';
  $affectValue ? $affectValue.observe('keyup', calculateEstimatePrice) : '';
  $affectStrategy ? $affectStrategy.observe('change', calculateEstimatePrice) : '';
  $pricingRule ? togglePricing($pricingRule) : '';
  fillProperties($$('#magetsync_form_category tr').findAll(function(el) { if (el.visible()) return el }).last().down('select'));
});
