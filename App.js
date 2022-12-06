import React, { useState } from "react";
import Map from "./Map";
import { Layers, TileLayer, VectorLayer } from "./Layers";
import { Style, Icon } from "ol/style";
import Feature from "ol/Feature";
import Point from "ol/geom/Point";
import { osm, vector } from "./Source";
import { fromLonLat, get } from "ol/proj";
import GeoJSON from "ol/format/GeoJSON";
import FeatureStyles from "./Features/Styles";

import mapConfig from "./config.json";
import "./App.css";

import addIcon from "./debugFiles/add.svg"
import mailIcon from "./debugFiles/mail.svg"

const geojsonObject = mapConfig.geojsonObject;
const geojsonObject2 = mapConfig.geojsonObject2;
const markersLonLat = [mapConfig.kansasCityLonLat, mapConfig.blueSpringsLonLat];

const App = () => {
  const [center, setCenter] = useState(fromLonLat(mapConfig.center));
  const [zoom, setZoom] = useState(16);

  return (
    <div id="bodyEncap">
      <div id="sideBarEncap">
        <div id="sideBarTop">
          <img src="https://media.discordapp.net/attachments/700130094844477561/961128316306350120/1610023331992.png" id="user_profile_pic">
          </img>
          User Account
          <hr className="horizontalSideReturn"></hr>
        </div>
        <div id="sideBarBot">
          <div>
            <hr className="horizontalSideReturn"></hr>
            <img src={mailIcon} className="img_icon"></img>
            <br></br>
            <img src={addIcon} className="img_icon"></img>
          </div>
        </div>
      </div>
      <div id="mapEncap">
        <Map center={center} zoom={zoom}>
          <Layers>
            <TileLayer source={osm()} zIndex={0} />
          </Layers>
        </Map>
        <div id="buttonBarsCont" onClick={()=>setCenter(fromLonLat([2.3522219,48.856614]))}>
          paris
        </div>
        <div id="buttonBarsCont3" onClick={()=>{
            if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition((position)=>{
                setCenter(fromLonLat([position.coords.longitude,position.coords.latitude]))
              });
            } else{
              setCenter(fromLonLat(mapConfig.center))
            }
            setZoom(18);
        }
        }>
          center
        </div>
      </div>
    </div>
  );
};

function createOverLayBox(internalText,eleClass,eleId){
  return 
    <div className="inputOverlay">
    </div>;
}

export default App;
