import './App.css';

// react
import React, { useState, useEffect } from 'react';

// openlayers
import GeoJSON from 'ol/format/GeoJSON'
import Feature from 'ol/Feature';

// components
import MapWrapper from './components/MapWrapper'
import { set } from 'ol/transform';

function App() {
  
  // set intial state
  const [ features, setFeatures ] = useState([])
  const [ center, setCenter] = useState([-121.955238,37.354107])
  
  return (
    <div className="App">
      <MapWrapper features={features} center={center} />
      <div id="guiCont">
        <div id="centerButton" onClick={()=>{
          if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition((position)=>{
              setCenter([position.coords.longitude,position.coords.latitude])
            })
          } else{
            setCenter([0,0])
          }
        }}>
          Center
        </div>
        <div id="newRequestButton">
          <div id="newRequestButtonText">
            +
          </div>
        </div>
      </div>
    </div>
  )
}

export default App