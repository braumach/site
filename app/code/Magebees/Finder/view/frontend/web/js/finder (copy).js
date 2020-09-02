define([
    "jquery",
    "jquery/ui"
], function (jQuery) {
    "use strict";
    //creating jquery widget
    jQuery.widget('magebees.cwsfinder',{
        selects: [],
        _create: function () {
            this.selects = jQuery('#finderDropdowns_'+this.options.finderId).find('select');
            if (this.selects[0].value!="") {
                document.getElementById('find_'+this.options.finderId).disabled = false;
            }
            for (var i = 1; i<=this.selects.length; i++) {
                if (this.selects[i-1].value=="NA") {
                    document.getElementById('drop_'+this.options.finderId+"_"+i).style.display = 'none';
                }
            }
            var finderId = this.options.finderId;
            jQuery('#'+this.selects[0].id).on('change',this,function () {
                if (this.value!="") {
                    document.getElementById('find_'+finderId).disabled = false;
                } else {
                    document.getElementById('find_'+finderId).disabled = true;
                }
            });
            this.selects.on('change', this, this._onChange);
            
            //reset all dropdowns on reset button
            jQuery('#reset_'+finderId).click(function () {
                var count = jQuery(this).attr('drop-down-counts');
                for (var j=1; j<=count; j++) {
                    if (j==1) {
                        document.getElementById("finder_"+finderId+"_"+(j)).selectedIndex=0;
                    } else {
                        document.getElementById("finder_"+finderId+"_"+(j)).selectedIndex=0;
                        jQuery('#finder_'+finderId+'_'+(j)).empty();
                        jQuery('#finder_'+finderId+"_"+(j)).append("<option value=''>Please Select</option>");
                        document.getElementById('drop_'+finderId+"_"+(j)).style.display = 'block';
                    }
                }
            });
        },
        
        _onChange: function (event) {
            var select = this;
            var selectedValue   = select.value;
            var self = event.data;
            var finderId = self.options.finderId;
            var dropdownId = jQuery(this).attr('drop-down-id');
            var fieldId = jQuery(this).attr('field-id');
            var dropdownCounts = jQuery(this).attr('drop-down-counts');
            var nextDropdown= parseInt(fieldId)+1;
            
            //For reset drop downs
            self._clearDropDowns(dropdownCounts,finderId,fieldId);
            //load next dropdown
            self._loadNextDropDown(event,selectedValue,finderId,fieldId,dropdownId,dropdownCounts);
            
        },//End _onChange
        
        _loadNextDropDown: function (event,selectedValue,finderId,fieldId,dropdownId,dropdownCounts) {
            var self = event.data;
            var url = self.options.url
            var searchurl = self.options.searchurl;
            var autosearch = self.options.autosearch;
            var category_id = self.options.category_id;
            var nextDropdown= parseInt(fieldId)+1;
            var finderString = "";
            
            for (var i=1; i<=fieldId; i++) {
                if (i!=1) {
                    finderString +=document.getElementById("finder_"+finderId+"_"+(i-1)).value+"-";
                }
            }
            
            //set finder string for form action
            if (selectedValue=="") {
                finderString = finderString.slice(0,-1);
            } else {
                finderString +=selectedValue;
            }
                        
            //set search page action
            //var searchurlparams = searchurl + finderId + "/" + encodeURIComponent(finderString);
            var searchurlparams = searchurl + encodeURIComponent(finderString);
            if (category_id) {
                searchurlparams = searchurlparams + "?cat=" + category_id;
            }
            jQuery("#finderform_"+finderId).attr('action', searchurlparams);
            
            //redirects to search page if last dropdown selects
            if (fieldId==dropdownCounts) {//for check last drop down
                if (autosearch && selectedValue != "") {
                    window.location.href = searchurlparams;
                }
            } else {
                if (selectedValue != "") {
                    jQuery.ajax({
                        url : url,
                        type: 'get',
                        data: { selectedValue : finderString,finderId : finderId,fieldId : fieldId,dropdownId : dropdownId ,category_id : category_id} ,
                        dataType: 'json',
                        showLoader:true,
                        success: function (data) {
                            if (data.length) {
                                var optionStr ="";
                                for (var i = 0; i < data.length; i++) {
                                    if (i==0) {
                                        optionStr ="<option value=''>Please Select</option>";
                                    }
                                    if (data[i]['id']=="NA") {
                                        optionStr = optionStr + '<option value="' + data[i]['id'] + '" selected>' + data[i]['name'] + '</option>';
                                    } else {
                                        optionStr = optionStr + '<option value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>';
                                    }
                                }
                                jQuery('#finder_'+finderId+'_'+nextDropdown).empty();
                                jQuery('#finder_'+finderId+'_'+nextDropdown).append(optionStr);
                                if (fieldId!=dropdownCounts) {
                                    var e = document.getElementById('finder_'+finderId+"_"+nextDropdown);
                                    var na_selected_value = e.options[e.selectedIndex].value;
                                                                                
                                    if (na_selected_value=="NA") {
                                        document.getElementById('drop_'+finderId+"_"+nextDropdown).style.display = 'none';
                                        fieldId = parseInt(fieldId)+1;
                                        dropdownId = parseInt(dropdownId)+1;
                                        self._loadNextDropDown(event,e.value,finderId,fieldId,dropdownId,dropdownCounts,url);
                                    } else {
                                        document.getElementById('drop_'+finderId+"_"+nextDropdown).style.display = 'block';
                                    }
                                }
                            } else {
                                jQuery('#finder_'+finderId+'_'+nextDropdown).empty();
                                jQuery('#finder_'+finderId+'_'+nextDropdown).append("<option value=''>Please Select</option>");
                                document.getElementById('drop_'+finderId+"_"+nextDropdown).style.display = 'block';
                            }
                        }
                    });
                }//End if
            }
        },
        
        //For reset drop downs
        _clearDropDowns: function (count,finderId,fieldId) {
            for (var j=1; j<count; j++) {
                if (j>=fieldId) {
                    document.getElementById("finder_"+finderId+"_"+(j+1)).selectedIndex=0;
                    jQuery('#finder_'+finderId+'_'+(j+1)).empty();
                    jQuery('#finder_'+finderId+"_"+(j+1)).append("<option value=''>Please Select</option>");
                    document.getElementById('drop_'+finderId+"_"+(j+1)).style.display = 'block';
                }
            }
        }
        
    });
    return jQuery.magebees.cwsfinder;
});



