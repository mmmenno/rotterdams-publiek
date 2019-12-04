<?php


?><!DOCTYPE html>
<html>
<head>
  
<title>Rotterdams Publiek - locaties</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>

  <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="styles.css" />

  
</head>
<body class="abt-locations">

  <div class="container-fluid">
    <div class="row">
      <div class="col-md-9 mapcol">
        <div id="bigmap"></div>
      </div>
      <div class="col-md black">
        <h3 id="itemtitle">Locaties</h3>

        <p id="itemtimes">times</p>

        <p id="itemtypes">types</p>

        <p id="itemnames">names</p>

        <p id="itemimage">image</p>

      </div>
    </div>
  </div>






<script>
  $(document).ready(function() {
    createMap();
    refreshMap();
  });

  function createMap(){
    center = [51.916857, 4.476839];
    zoomlevel = 14;
    
    map = L.map('bigmap', {
          center: center,
          zoom: zoomlevel,
          minZoom: 1,
          maxZoom: 20,
          scrollWheelZoom: true,
          zoomControl: false
      });

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}{r}.{ext}', {
      attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      subdomains: 'abcd',
      minZoom: 0,
      maxZoom: 20,
      ext: 'png'
    }).addTo(map);
  }

  function refreshMap(){
    $.ajax({
          type: 'GET',
          url: 'locaties.geojson',
          dataType: 'json',
          success: function(jsonData) {
            if (typeof monuments !== 'undefined') {
              map.removeLayer(monuments);
            }

            monuments = L.geoJson(null, {
              pointToLayer: function (feature, latlng) {                    
                  return new L.CircleMarker(latlng, {
                      color: "#FC2211",
                      radius:8,
                      weight: 2,
                      opacity: 0.8,
                      fillOpacity: 0.3
                  });
              },
              style: function(feature) {
                return {
                    color: getColor(feature.properties),
                    clickable: true
                };
              },
              onEachFeature: function(feature, layer) {
                layer.on({
                    click: whenClicked
                  });
                }
              }).addTo(map);

              monuments.addData(jsonData).bringToFront();
          
              //map.fitBounds(monuments.getBounds());
              //$('#straatinfo').html('');
          },
          error: function() {
              console.log('Error loading data');
          }
      });
  }

  function getColor(props) {

    if (typeof props['bend'] == 'undefined' || props['bend'] == null) {
      return '#950305';
    }
    return '#738AB7';
  }

  function whenClicked(){
    $("#intro").hide();

    var props = $(this)[0].feature.properties;
    console.log(props);
    $("#itemtitle").html(props['label']);

    var years = '';
    if(props['bstart'] != null){
      years += 'gebouwd in ' + props['bstart'].substring(0, 4);
    }
    if(props['bstart'] != null && props['bend'] != null){
      years += '<br />';
    }
    if(props['bend'] != null){
      years += 'verdwenen in ' + props['bend'].substring(0, 4);
    }
    $("#itemtimes").html(years);

    var types = '';
    props['types'].forEach(function(item,index){
      types += item.label + '<br />';
      if(item.start != null){
        types += 'van ' + item.start.substring(0, 4) + ' ';
      }
      if(item.end != null){
        types += 'tot ' + item.end.substring(0, 4) + '<br />';
      }

    });
    $("#itemtypes").html(types);

    var names = '';
    props['names'].forEach(function(item,index){
      names += item.name + '<br />';
      if(item.start != null){
        names += 'van ' + item.start.substring(0, 4) + ' ';
      }
      if(item.end != null){
        names += 'tot ' + item.end.substring(0, 4) + '<br />';
      }

    });
    $("#itemnames").html(names);

    if(props['image'] != null){
      $("#itemimage").html('<img style="width: 100%;" src="' + props['image'] + '?width=300px" />');
    }else{
      $("#itemimage").html('');
    }
    
    $("#monumentlabel").html('<a target="_blank" href="' + props['wdid'] + '">' + props['label'] + '</a>');
    $("#monumentlink").html('<a target="_blank" href="https://monumentenregister.cultureelerfgoed.nl/monumenten/' + props['mnr'] + '">monument ' + props['mnr'] + '</a>');
    if(props['bagid']!=null){
      $("#monumentbag").html('<a target="_blank" href="http://bag.basisregistraties.overheid.nl/bag/id/pand/' + props['bagid'] + '">' + props['bagid'] + '</a>');
    }else{
      $("#monumentbag").html('');
    }
    
    
  }

</script>



</body>
</html>
