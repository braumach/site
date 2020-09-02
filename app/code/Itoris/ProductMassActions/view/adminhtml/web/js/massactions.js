/**
 * Copyright © 2016 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
window.iProductMassActions = {
	modalPlaceholder: '.modal-popup.confirm .modal-content > div',
	extraPostData: {},
	
	init: function(registry, utils, is_enabled){
		this.registry = registry;
		this.utils = utils;
		var items = jQuery('#container ul.action-menu > li');
		var items2 = jQuery('.sticky-header ul.action-menu > li');
		var massaction = this.registry.get('product_listing.product_listing.listing_top.listing_massaction');
		if (!massaction || !items.length || !massaction.getAction("mass_attribute_set")) { //wait for massaction
			setTimeout(function(){window.iProductMassActions.init(registry, utils, is_enabled);}, 500);
			return;
		}
		defaultAttributes = {method: 'post',enctype: 'multipart/form-data'};
		var func = this.utils.submit.toString().replace('data.form_key', 'window.iProductMassActions.getExtraParams(data);data.form_key');
		var func = func.replace("data['form_key']", "window.iProductMassActions.getExtraParams(data);data['form_key']");
		eval('this.utils.submit='+func);
		
		var index = massaction.actions().indexOf(massaction.getAction("mass_attribute_set"));
		if (items[index]) {
			items[index].style.borderTop = '1px solid #aaa';
			for(i=index; i<index+9; i++) if (items[i]) items[i].style.color = '#007';
			if (!is_enabled) for(i=index; i<index+9; i++) if (items[i]) items[i].style.display = 'none';
		}
		if (items2[index]) {
			items2[index].style.borderTop = '1px solid #aaa';
			for(i=index; i<index+9; i++) if (items2[i]) items2[i].style.color = '#007';
			if (!is_enabled) for(i=index; i<index+9; i++) if (items2[i]) items2[i].style.display = 'none';
		}
		if (items[index+9]) items[index+9].style.borderTop = '1px solid #aaa';
		if (items2[index+9]) items2[index+9].style.borderTop = '1px solid #aaa';
	},
    
    getAction: function(type) {
        var massaction = this.registry.get('product_listing.product_listing.listing_top.listing_massaction');
        var action = massaction.getAction(type);
        if (!action) {
            jQuery.each(['copy_custom_options', 'copy_relations', 'copy_upsells', 'copy_crosssells', 'copy_images', 'copy_categories'], function(index, parent){
                var parentAction = massaction.getAction(parent);
                if (parentAction.actions) jQuery.each(parentAction.actions, function(i, _action){
                    if (_action && _action.type == parent+'.'+type) action = _action;
                })
            });
        }
        return action;
    },
	
	getExtraParams: function(data) {
		jQuery.extend(data, this.extraPostData);
	},
	
	attachMassActionEvent: function(type){
		var container = jQuery(this.modalPlaceholder);
		if (!container[0]) { //wait for container
			setTimeout(function(){window.iProductMassActions.attachMassActionEvent(type)}, 100);
			return;
		}
		window.massActionObject = this.getAction(type);
		window.massActionURL = window.massActionObject.url;
		
		var popup = container.closest('.modal-popup');
		container.append(jQuery('<span>').html(this.translations[type+'_msg']));
		var input = jQuery('<input>').attr({'type': 'text', id: 'mass_action_from_pid'}).css({margin: '0px 10px'});
		container.append(input);
		container.closest('.modal-popup').find('button.action-accept').attr('disabled', 'disabled');
		input.on('keyup', function(){
			if (this.value != "") {
				popup.find('button.action-accept').removeAttr('disabled');
			} else {
				popup.find('button.action-accept').attr('disabled', 'disabled');
			}
			window.massActionObject.url = window.massActionURL + 'from_product_id/'+this.value;
		});
		setTimeout(function(){input.focus()}, 100);
		
		var showList = jQuery('<span>').html('<a href="#" id="mass_action_pickup" onclick="window.iProductMassActions.pickUpProduct(this)">Not sure?</a>');
		container.append(showList);
	},
	
	attachMassActionEventCategory: function(type){
		var container = jQuery(this.modalPlaceholder);
		if (!container[0]) { //wait for container
			setTimeout(function(){window.iProductMassActions.attachMassActionEventCategory(type)}, 100);
			return;
		}
		var massaction = this.registry.get('product_listing.product_listing.listing_top.listing_massaction');
		window.massActionObject = this.getAction(type);
		window.massActionURL = window.massActionObject.url;
		
		var popup = container.closest('.modal-popup');
		container.append(jQuery('<div>').html(this.translations[type+'_msg']));
		var select = jQuery('<select>').attr({'size': '10', 'multiple': 'multiple', id: 'mass_action_cat_id'}).css({margin: '10px 0px', 'min-width': '400px'});
		if (type == 'remove_categories') {
			select.append(jQuery('<option>').attr({value: -1, selected: 'selected'}).html('--- Unassign all categories'));
			window.massActionObject.url = window.massActionURL + 'catid/-1';
		} else {
			container.closest('.modal-popup').find('button.action-accept').attr('disabled', 'disabled');
		}
		jQuery(this.categories).each(function(index, category){
			for(var i=1, space = ''; i<category.level; i++) space += '&nbsp;&nbsp;&nbsp;';
			var option = jQuery('<option>').attr({value: category.id}).html(space+category.name);
			select.append(option);
		});
		container.append(select);		
		select.on('change', function(){
			var values = [];
			jQuery('#mass_action_cat_id option').each(function(index, option){
				if (option.selected) values[values.length] = option.value;
			});
			if (values.length > 0) {
				popup.find('button.action-accept').removeAttr('disabled');
			} else {
				popup.find('button.action-accept').attr('disabled', 'disabled');
			}
			window.massActionObject.url = window.massActionURL + 'catid/'+values.join();
		});
	},
    
	attachMassActionEventAttributeSet: function(type){
		var container = jQuery(this.modalPlaceholder);
		if (!container[0]) { //wait for container
			setTimeout(function(){window.iProductMassActions.attachMassActionEventAttributeSet(type)}, 100);
			return;
		}
		var massaction = this.registry.get('product_listing.product_listing.listing_top.listing_massaction');
		window.massActionObject = this.getAction(type);
		window.massActionURL = window.massActionObject.url;
		
		var popup = container.closest('.modal-popup');
		container.append(jQuery('<div>').html(this.translations[type+'_msg']));
		var select = jQuery('<select>').attr({id: 'mass_action_attr_set_id'}).css({margin: '10px 0px', 'min-width': '400px'});
        select.append(jQuery('<option>').attr({value: -1, selected: 'selected'}).html('-- Select Attribute Set --'));
        window.massActionObject.url = window.massActionURL + 'attr_set_id/-1';
        container.closest('.modal-popup').find('button.action-accept').attr('disabled', 'disabled');
		jQuery(this.attributeSets).each(function(index, attributeSet){
			var option = jQuery('<option>').attr({value: attributeSet.id}).html(attributeSet.name);
			select.append(option);
		});
		container.append(select);		
		select.on('change', function(){
			if (select.val()) {
				popup.find('button.action-accept').removeAttr('disabled');
			} else {
				popup.find('button.action-accept').attr('disabled', 'disabled');
			}
			window.massActionObject.url = window.massActionURL + 'attr_set_id/'+select.val();
		});
	},
	
	attachMassActionEventAttribute: function(type){
		this.extraPostData = {};
		var container = jQuery(this.modalPlaceholder);
		if (!container[0]) { //wait for container
			setTimeout(function(){window.iProductMassActions.attachMassActionEventAttribute(type)}, 100);
			return;
		}
		//container.css({'text-align': 'center'});
		var massaction = this.registry.get('product_listing.product_listing.listing_top.listing_massaction');
		window.massActionObject = this.getAction(type);
		window.massActionURL = window.massActionObject.url;
		
		var popup = container.closest('.modal-popup');
		container.append(jQuery('<div>').html(this.translations[type+'_msg']));
		var select = jQuery('<select>').attr({id: 'mass_action_attribute'});
		select.append(jQuery('<option>').attr({value: '', selected: 'selected'}).html('-- Please select an attribute --'));
		var optgroup = false;
        jQuery(this.attributes).each(function(index, attribute){
            if (attribute.grouplabel) {
                optgroup = jQuery('<optgroup>').attr('label', attribute.name).appendTo(select);
                return;
            }
            var a = jQuery('<option>').attr({value: index}).html(attribute.name);
			if (attribute.name) (optgroup ? optgroup : select).append(a);
		});
		container.append(select);
		container.append(jQuery('<div>').attr({id: 'mass_attribute_extra'}));
		container.closest('.modal-popup').find('button.action-accept').attr('disabled', 'disabled');
		select.on('change', function(){
			window.iProductMassActions.extraPostData = {'storeId': jQuery('.admin__data-grid-filters select[name="store_id"]').val()};
			jQuery('#mass_attribute_extra').html('');
			if (this.selectedIndex == 0) {
				popup.find('button.action-accept').attr('disabled', 'disabled');
				return;
			}
			popup.find('button.action-accept').removeAttr('disabled');			
			var attribute = window.iProductMassActions.attributes[this.value], t = false, s = false;
			if (attribute.input == 'select' || attribute.input == 'multiselect' || attribute.input == 'boolean') {
				if (attribute.input != 'multiselect') jQuery('#mass_attribute_extra').html('<div>Select a value from the list:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				if (attribute.input == 'multiselect') jQuery('#mass_attribute_extra').html('<div>Select one or more values from the list:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				var s = jQuery('<select>').css({'min-width': '400px'});
				if (attribute.input == 'multiselect') s.attr({size:8, multiple: 'multiple'});
				if (!attribute.is_required) s.append(jQuery('<option>').attr({value: ''}).html('&nbsp;&nbsp;&nbsp;-- no value'));
				jQuery(attribute.options).each(function(index, option){
					var o = jQuery('<option>').attr({value: option.value}).html(option.label);
					if (option.is_default) o.attr({selected: 'selected'});
					s.append(o);
				});
				jQuery('#mass_attribute_extra').append(s);
			} else if (attribute.input == 'textarea') {
				jQuery('#mass_attribute_extra').html('<div>Enter a new value:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				var t = jQuery('<textarea>').css({'width': '95%', 'height': '200px'});
				jQuery('#mass_attribute_extra').append(t);
                jQuery('#mass_attribute_extra').append('<br /><br /><span style="color:blue">Note. You can use other attributes within the text as {{attribute_code}}<span>');
			} else if (attribute.input == 'price') {
				jQuery('#mass_attribute_extra').html('<div>Enter a value:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				var t = jQuery('<input>').attr({type: 'text', value: '0.00'});
				jQuery('#mass_attribute_extra').append(t);
				var tp = jQuery('<select><option value="0">Fixed</option><option value="1">Percent</option></select>');
				jQuery('#mass_attribute_extra').append(tp);
			} else if (attribute.input == 'int') {
				jQuery('#mass_attribute_extra').html('<div>Enter a value:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				var t = jQuery('<input>').attr({type: 'text', value: '0'});
				jQuery('#mass_attribute_extra').append(t);
			} else {
				jQuery('#mass_attribute_extra').html('<div>Enter a new value:'+(attribute.is_required ? ' <span style="color:red">*</span>' : '')+'</div>');
				var t = jQuery('<input>').attr({type: 'text'}).css({'width': '95%'});
				jQuery('#mass_attribute_extra').append(t);
                jQuery('#mass_attribute_extra').append('<br /><br /><span style="color:blue">Note. You can use other attributes within the text as {{attribute_code}}<span>');
			}
            if (jQuery('.admin__data-grid-filters select[name="store_id"]').val() > 0) {
                (s ? s : t).after('<input type="checkbox" id="pma_use_default" style="margin:0 5px 0 10px" onclick="jQuery(this).prev()[0].disabled = this.checked;" /><label for="pma_use_default">Use default</label><br />');
                jQuery('#pma_use_default').on('change', function(){window.iProductMassActions.extraPostData.massAttributeUseDefault = this.checked ? 1 : 0 }).trigger('change');
            }
			if (attribute.input == 'price' || attribute.input == 'multiselect' || attribute.input == 'int') {
				var m = jQuery('<select>');
				m.append(jQuery('<option>').attr({value: 0}).html('Replace'));
				m.append(jQuery('<option>').attr({value: 1, selected: 'selected'}).html('Add'));
				m.append(jQuery('<option>').attr({value: 2}).html('Subtract'));
				jQuery('#mass_attribute_extra').append('<div>Choose a method of update: </div>');
				jQuery('#mass_attribute_extra').append(m);				
			}
            if (attribute.input == 'price') {
				var bt = jQuery('<div>Select attribute the update is based on</div>');
				jQuery('#mass_attribute_extra').append(bt);
				var b = select.clone();
                b.find('option')[0].innerHTML = '-- based on current attribute --';
				jQuery('#mass_attribute_extra').append(b);
				var bn = jQuery('<div></div>').css({'margin-top': '10px', 'color': 'blue'});
				jQuery('#mass_attribute_extra').append(bn);
                b.updateNote = function(){
                    var _a = window.iProductMassActions.attributes[select.val()].code;
                    var _b = window.iProductMassActions.attributes[b.val()], _b = _b && _b.code ? _b.code : _a;
                    bn.text('Note, the update is going to be: {{'+_a+'}} = {{'+_b+'}}' + (parseFloat(t.val()) ? (m.val() == 1 ? ' + ' : ' - ') + t.val() + (tp.val()-0 ? '%' : '') : '') );
					if (_a == 'cost') bn.html(bn.text() + '<br /><span style="color:red">Also remember that attribute <b>Cost</b> in NOT the same to attribute <b>Price</b>!</a>');
                }
                b.updateNote();
			}
			if (s) s.on('change', function(){window.iProductMassActions.extraPostData.massAttributeValue = jQuery(this).val().join ? jQuery(this).val().join() : jQuery(this).val(); });
			if (t) t.on('change', function(){
                window.iProductMassActions.extraPostData.massAttributeValue = jQuery(this).val();
                if (b) b.updateNote();
            });
			if (m) m.on('change', function(){
                window.iProductMassActions.extraPostData.massAttributeMethod = jQuery(this).val();
                if (tp) tp.css({display: this.value-0 ? 'inline' : 'none'});
                if (b) b.css({display: this.value-0 ? 'inline' : 'none'});
                if (bt) bt.css({display: this.value-0 ? 'block' : 'none'});
                if (bn) bn.css({display: this.value-0 ? 'block' : 'none'});
                if (b) b.updateNote();
            });
            if (tp) tp.on('change', function(){
                window.iProductMassActions.extraPostData.massAttributeValueType = jQuery(this).val();
                if (b) b.updateNote();
            });
            if (b) b.on('change', function(){
                window.iProductMassActions.extraPostData.massAttributeValueBase = window.iProductMassActions.attributes[this.value].code;
                if (b) b.updateNote();
            });
			window.iProductMassActions.extraPostData.massAttributeValue = t ? t.val() : (s ? s.val().join ? s.val().join() : s.val() : '');
			window.iProductMassActions.extraPostData.massAttributeMethod = m ? m.val() : '';
			window.iProductMassActions.extraPostData.massAttribute = window.iProductMassActions.attributes[this.value].code;
		});
	},
	
	pickUpProduct: function(link){
		link.style.display = 'none';
		container = jQuery(link.parentNode.parentNode);
		var grid = jQuery('#mass_action_pickup_div');		
		if (grid[0]) {
			grid.css({display: 'block'});
		} else {
			var grid = jQuery('<div>').attr({id: 'mass_action_pickup_div'});
			grid.html('<div style="text-align:center; padding:20px 0px"><i>Pick up a product from the list. Please wait, loading products...</i><br /><img src="'+window.iProductMassActions.loadingImageUrl+'" /></div>');
			container.append(grid);
		}
		jQuery.ajax({
			url: this.pickUpProductGridUrl,
			method: 'GET',
			success: function(resp){
				grid.html(resp);
			}
		});		
	},
	
	addProductLinkToItem: function(pid){
		jQuery('#mass_action_from_pid')[0].value = pid;
		window.massActionObject.url = window.massActionURL + 'from_product_id/'+pid;
		jQuery('#mass_action_pickup').css({display: 'inline'});
		jQuery('#mass_action_pickup_div').css({display: 'none'});	
		jQuery('#mass_action_from_pid').closest('.modal-popup').find('button.action-accept').removeAttr('disabled');
	}

}
