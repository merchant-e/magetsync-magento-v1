var ajaxUrl, categoryReloadUrl, categoryListingId, categoryTemplateId, placeholder;

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
            var delta = priceValue * Number(affectPriceVal.value) / 100;
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
        $('is_supply').observe('change', function (e) {
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

function fillProperties(selectElement) {
    if (!selectElement || selectElement.value == '' || !ajaxUrl) {
        $('properties_holder').update(placeholder);
        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('properties_holder').update('');
            $('properties_holder').update('Searching…');
        },
        onComplete: function (subform) {
            var parsedResponse = JSON.parse(subform.responseText);
            if (parsedResponse.taxonomy != '') {
                updateTaxonomy(parsedResponse.taxonomy);
            }
        }
    });
}

function updateTaxonomy(taxonomy) {
    $('properties_holder').update(taxonomy);
    if ($('size_scales')) {
        handleSizeScales();
        $('size_scales').observe('change', handleSizeScales);
    }
}

function generateNewHtml(sizeScalesValuesObj, scaleVal) {
    var newHTML = '';
    for (var key in sizeScalesValuesObj) {
        if (sizeScalesValuesObj.hasOwnProperty(key) && sizeScalesValuesObj[key].scale == scaleVal) {
            newHTML += '<option value="' + sizeScalesValuesObj[key].value + '">' + sizeScalesValuesObj[key].label + '</option>';
        }
    }

    return newHTML;
}

function handleSizeScales() {
    var $sizeScales = $('size_scales');
    var $sizeScalesValues = $('size_scales_values');
    if ($sizeScales && $sizeScalesValues) {
        var sizeScalesValuesObj = JSON.parse($sizeScalesValues.value);
        var $sizeValues = $sizeScales.up('span').next('span');
        if (!$sizeScales.value) {
            $sizeScales.setStyle({'float': 'none'});
            $sizeValues.hide();
        } else {
            var newHTML = generateNewHtml(sizeScalesValuesObj, $sizeScales.value);
            $sizeValues.down('select').update(newHTML);
            $sizeScales.setStyle({'float': 'left'});
            $sizeValues.down('label').setStyle({'margin-left': '50px'});
            $sizeValues.show();
        }
    }
}

function getCategory(selectElement) {

    $('subcategory_id').up(0).up(0).hide();
    $('subsubcategory_id').up(0).up(0).hide();
    $('subcategory4_id').up(0).up(0).hide();
    $('subcategory5_id').up(0).up(0).hide();
    $('subcategory6_id').up(0).up(0).hide();
    $('subcategory7_id').up(0).up(0).hide();

    if (selectElement.value == '' || !ajaxUrl) {
        $('subcategory_id').update('');
        $('subsubcategory_id').update('');
        $('subcategory4_id').update('');
        $('subcategory5_id').update('');
        $('subcategory6_id').update('');
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties();

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subcategory_id').update('');
            $('subcategory_id').update('Searching…');
        },
        onComplete: function (subform) {
            var parsedResponse = JSON.parse(subform.responseText);
            if (parsedResponse.categories == '') {
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
                    updateTaxonomy(parsedResponse.taxonomy);
                }
            }
        }
    });
}

function getSubCategory(selectElement) {
    $('subsubcategory_id').up(0).up(0).hide();
    $('subcategory4_id').up(0).up(0).hide();
    $('subcategory5_id').up(0).up(0).hide();
    $('subcategory6_id').up(0).up(0).hide();
    $('subcategory7_id').up(0).up(0).hide();

    if (selectElement.value == '' || !ajaxUrl) {
        $('subsubcategory_id').update('');
        $('subcategory4_id').update('');
        $('subcategory5_id').update('');
        $('subcategory6_id').update('');
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties($('category_id'));

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subsubcategory_id').update('');
            $('subsubcategory_id').update('Searching…');
        },
        onComplete: function (subform) {
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
                    updateTaxonomy(parsedResponse.taxonomy);
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

    if (selectElement.value == '' || !ajaxUrl) {
        $('subcategory4_id').update('');
        $('subcategory5_id').update('');
        $('subcategory6_id').update('');
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties($('subcategory_id'));

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subcategory4_id').update('');
            $('subcategory4_id').update('Searching…');
        },
        onComplete: function (subform) {
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
                    updateTaxonomy(parsedResponse.taxonomy);
                }
            }
        }
    });
}

function getSubCategory4(selectElement) {
    $('subcategory5_id').up(0).up(0).hide();
    $('subcategory6_id').up(0).up(0).hide();
    $('subcategory7_id').up(0).up(0).hide();

    if (selectElement.value == '' || !ajaxUrl) {
        $('subcategory5_id').update('');
        $('subcategory6_id').update('');
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties($('subsubcategory_id'));

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subcategory5_id').update('');
            $('subcategory5_id').update('Searching…');
        },
        onComplete: function (subform) {
            var parsedResponse = JSON.parse(subform.responseText);
            if (parsedResponse.categories == '') {
                $('subcategory5_id').up(0).up(0).hide();
                $('subcategory6_id').up(0).up(0).hide();
                $('subcategory7_id').up(0).up(0).hide();
            } else {
                $('subcategory5_id').up(0).up(0).show();
                $('subcategory5_id').update(parsedResponse.categories);
                if (parsedResponse.taxonomy != '') {
                    updateTaxonomy(parsedResponse.taxonomy);
                }
            }
        }
    });
}

function getSubCategory5(selectElement) {
    $('subcategory6_id').up(0).up(0).hide();
    $('subcategory7_id').up(0).up(0).hide();

    if (selectElement.value == '' || !ajaxUrl) {
        $('subcategory6_id').update('');
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties($('subcategory4_id'));

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subcategory6_id').update('');
            $('subcategory6_id').update('Searching…');
        },
        onComplete: function (subform) {
            var parsedResponse = JSON.parse(subform.responseText);
            if (parsedResponse.categories == '') {
                $('subcategory6_id').up(0).up(0).hide();
                $('subcategory7_id').up(0).up(0).hide();
            } else {
                $('subcategory6_id').up(0).up(0).show();
                $('subcategory6_id').update(parsedResponse.categories);
                if (parsedResponse.taxonomy != '') {
                    updateTaxonomy(parsedResponse.taxonomy);
                }
            }
        }
    });
}

function getSubCategory6(selectElement) {
    $('subcategory7_id').up(0).up(0).hide();

    if (selectElement.value == '' || !ajaxUrl) {
        $('subcategory7_id').update('');
        if (ajaxUrl) fillProperties($('subcategory5_id'));

        return false;
    }
    var reloadurl = ajaxUrl + '/tag/' + selectElement.value;
    new Ajax.Request(reloadurl, {
        method: 'get',
        onLoading: function () {
            $('subcategory7_id').update('');
            $('subcategory7_id').update('Searching…');
        },
        onComplete: function (subform) {
            var parsedResponse = JSON.parse(subform.responseText);
            if (parsedResponse.categories == '') {
                $('subcategory7_id').up(0).up(0).hide();
            } else {
                $('subcategory7_id').up(0).up(0).show();
                $('subcategory7_id').update(parsedResponse.categories);
                if (parsedResponse.taxonomy != '') {
                    updateTaxonomy(parsedResponse.taxonomy);
                }
            }
        }
    });
}

function enableStyle2(selectElement) {
    if (selectElement.value == '') {
        $('style_two').value = '';
        $('style_two').up(0).up(0).hide();
    } else {
        $('style_two').up(0).up(0).show();
    }
}

document.observe('dom:loaded', function () {
    categoryReloadUrl = $('category_reload_url').value;
    categoryListingId = $('category_listing_id');
    categoryTemplateId = $('category_template_id');
    // Detect Listing or Attribute Template
    if (categoryListingId != null) {
        ajaxUrl = categoryReloadUrl + 'listing/' + categoryListingId.value;
    } else {
        ajaxUrl = categoryTemplateId != null ? categoryReloadUrl + 'template/' + categoryTemplateId.value : false;
    }

    placeholder = $('placeholder').innerHTML;
    var $customPrice = $('custom-price');
    var $affectValue = $('affect_value');
    var $affectStrategy = $('affect_strategy');
    var $pricingRule = $('pricing_rule');
    $customPrice ? $customPrice.observe('change', tootglePriceInput) : '';
    $affectValue ? $affectValue.observe('keyup', calculateEstimatePrice) : '';
    $affectStrategy ? $affectStrategy.observe('change', calculateEstimatePrice) : '';
    $pricingRule ? togglePricing($pricingRule) : '';
    // Retrieve taxonomy for last visible category select
    fillProperties($$('#magetsync_form_category tr').findAll(function (el) {
        if (el.visible()) return el
    }).last().down('select'));
    // Show next empty select if some categories are preselected
    // Used for initial page load
    var $lastVisibleNotEmptySelect = $$('#magetsync_form_category tr').findAll(function (el) {if (el.visible() && el.down('select').value) return el}).last();
    if ($lastVisibleNotEmptySelect) $lastVisibleNotEmptySelect.next('tr').show();
});
