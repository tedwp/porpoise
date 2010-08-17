/*
 * PorPOISe
 * Copyright 2009 SURFnet BV
 * Thanks to PLKT for the Google maps widget (http://plkt.free.fr)
 * Released under a permissive license (see LICENSE)
 */

var GUI = {
	addPOIAction: function (source) {
		var maxIndex = 0;

		var poiActionTables = document.body.select("table.poi table.action");
		for (var i = 0; i < poiActionTables.length; i++) {
			var inputs = poiActionTables[i].select("input");
			if (inputs.length == 0) {
				/* weird, page must be corrupt */
				return;
			}
			var inputName = inputs[0].name;
			var indexWithBrackets = inputName.match(/\[.+\]/);
			if (indexWithBrackets.length == 0) {
				/* again, invalid */
				return;
			}
			var index = parseInt(indexWithBrackets[0].substr(1, indexWithBrackets[0].length - 2));
			if (index > maxIndex) {
				maxIndex = index;
			}
		}

		var newIndex = maxIndex + 1;

		var newRow = document.createElement("tr");
		var td = document.createElement("td");
		td.insert("Action<br><button type=\"button\" onclick=\"GUI.removePOIAction(" + newIndex + ")\">Remove</button>");
		newRow.appendChild(td);
		td = document.createElement("td");
		newRow.appendChild(td);
		new Ajax.Updater ({ success: td }, "gui.php", { parameters: { action: "newAction", index: newIndex }, insertion: "bottom" } );
		/*var actionTable = document.createElement("table");
		td.appendChild(actionTable);
		actionTable.addClassName("action");
		for (var i = 0; i < 4; i++) {
			var tr = document.createElement("tr");
			var labelTd = document.createElement("td");
			var valueTd = document.createElement("td");
			if (i == 0) {
				labelTd.textContent = "Label";
				var input = document.createElement("input");
				input.type = "text";
				input.name = "actions[" + newIndex + "][label]";
				valueTd.appendChild(input);
			} else if (i == 1) {
				labelTd.textContent = "URI";
				var input = document.createElement("input");
				input.type = "text";
				input.name = "actions[" + newIndex + "][uri]";
				valueTd.appendChild(input);
			} else if (i == 2) {
				labelTd.textContent = "Auto-trigger range";
				var input = document.createElement("input");
				input.type = "text";
				input.name = "actions[" + newIndex + "][autoTriggerRange]";
				input.size = 2;
				valueTd.appendChild(input);
			} else if (i == 3) {
				labelTd.textContent = "Auto-trigger only";
				var select = document.createElement("select");
				select.name = "actions[" + newIndex + "][autoTriggerOnly]";
				var option = document.createElement("option");
				option.textContent = "Yes";
				option.value = 1;
				select.appendChild(option);
				option = document.createElement("option");
				option.textContent = "No";
				option.value = 0;
				option.selected = "selected";
				select.appendChild(option);
				valueTd.appendChild(select);
			} else {
				// 'scuse me? 
				continue;
			}
			tr.appendChild(labelTd);
			tr.appendChild(valueTd);
			actionTable.appendChild(tr);
		}*/
		var sourceRow = source.up("tr");
		sourceRow.insert({ before: newRow });
	}

	, removePOIAction: function(indexToRemove) {
		var poiActionTables = document.body.select("table.poi table.action");
		for (var i = 0; i < poiActionTables.length; i++) {
			var inputs = poiActionTables[i].select("input");
			if (inputs.length == 0) {
				/* weird, page must be corrupt */
				return;
			}
			var inputName = inputs[0].name;
			var indexWithBrackets = inputName.match(/\[.+\]/);
			if (indexWithBrackets.length == 0) {
				/* again, invalid */
				return;
			}
			var index = parseInt(indexWithBrackets[0].substr(1, indexWithBrackets[0].length - 2));
			if (index == indexToRemove) {
				poiActionTables[i].up("tr").remove();
				return;
			}
		}
	}		
}



/*
  copyright (c) 2009 Google inc.

  You are free to copy and use this sample.
  License can be found here: http://code.google.com/apis/ajaxsearch/faq/#license
*/

/*
-------------------------------------------------------
--- Porpoise POI coords selection unsing Google Map ---
-------------------------------------------------------
---------- PLKT http://plkt.free.fr -------------------
-------------------------------------------------------

\[^-^]/ !

*/

////////////////////////////////////////        VARS

// Objects
var map;                        
var geocoder;   

// Nodes
var addressInput
var mapPopin;
var mapDiv;
                
////////////////////////////////////////        FUNCTIONS

// Find place on the map
function geocode(){
        geocoder.geocode(
                {
                        'address': addressInput.value,
                        'partialmatch': true
                },
                function(results, status){
                        if (status == 'OK' && results.length > 0) {
                                map.fitBounds(results[0].geometry.viewport);
                        }
                        else{
                                alert("Geocode was not successful for the following reason: " + status);
                        }
                }
        );
}

// Onload function
function initialize(){

        //////////////////////////////////////////////////
        // Here : porpoising the script
                // Creating "map popin" nodes ( Non Intrusive :)
                mapPopinLink=document.createElement('input');
                mapPopinLink.type="button";
                mapPopinLink.value="Find on Google Map";
                mapPopinLink.onclick=function(){
                        mapPopin.style.visibility="visible";
                };
                mapPopin=document.createElement('div');
                mapPopin.style.visibility="hidden";
                mapPopin.style.position="absolute";
                mapPopin.style.background="#FFFFFF";
                mapPopin.style.border="solid 1px #000000";
                mapPopin.style.padding="1em";
                mapPopin.style.marginTop="1em";
                mapPopin.style.marginLeft="-6em";
                        niceDisplay=document.createElement('p');
                                addressInput=document.createElement('input');
                                addressInput.type="text";
                                addressInput.style.width="300px";
                                        niceDisplay.appendChild(addressInput);
                                findPlaceButton=document.createElement('input');
                                findPlaceButton.type="button";
                                findPlaceButton.value="Find Place";
                                findPlaceButton.onclick=function(){ geocode(); };
                                        niceDisplay.appendChild(findPlaceButton);
                                CloseButton=document.createElement('input');
                                CloseButton.type="button";
                                CloseButton.value="CLOSE";
                                CloseButton.style.marginLeft="1.5em";
                                CloseButton.onclick=function(){ mapPopin.style.visibility="hidden"; };
                                        niceDisplay.appendChild(CloseButton);
                        mapPopin.appendChild(niceDisplay);
                        mapDiv=document.createElement('div');
                        mapDiv.style.display="block";
                        mapDiv.style.width="500px";
                        mapDiv.style.height="400px";
                        mapDiv.innerHTML="ok";
                                mapPopin.appendChild(mapDiv);
                porpoiselnginputs=document.getElementsByName('lon');
                porpoiselnginputs[0].parentNode.appendChild(mapPopinLink);
                porpoiselnginputs[0].parentNode.appendChild(mapPopin);
        //////////////////////////////////////////////////

        map=new google.maps.Map(
                mapDiv,
                {
                        center: new google.maps.LatLng(43.604442, 1.443333),
                        zoom: 4,
                        mapTypeId: google.maps.MapTypeId.HYBRID
                }
        );

        geocoder=new google.maps.Geocoder();

        // Add event to the map ( when user click on the map ... )
        var infoWindow=new google.maps.InfoWindow();
        google.maps.event.addListener(
                map,
                'click',
                function(event){
                        var lat=event.latLng.lat();     // latitude
                        var lng=event.latLng.lng();     // longitude
                                var html='<strong>Lat:</strong><br >'+lat+'<br ><strong>Long:</strong><br >'+lng;
                                infoWindow.setContent(html);
                                infoWindow.setPosition(event.latLng);
                                infoWindow.open(map);
                        //////////////////////////////////////////////////
                        // Here : porpoising the script
                        porpoiselatinputs=document.getElementsByName('lat');
                        porpoiselatinputs[0].value=lat;
                        porpoiselnginputs=document.getElementsByName('lon');
                        porpoiselnginputs[0].value=lng;
                        //////////////////////////////////////////////////
                }
        );

}

////////////////////////////////////////        ONLOAD

google.maps.event.addDomListener(window, 'load', initialize);
