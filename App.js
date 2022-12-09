import './App.css';

// react
import React, { useState, useEffect } from 'react';

// openlayers
import GeoJSON from 'ol/format/GeoJSON'
import Feature from 'ol/Feature';

// components
import MapWrapper from './components/MapWrapper'

function App() {
  
  // set intial state
  const [ features, setFeatures ] = useState([])
  const [ center, setCenter] = useState([-121.955238,37.354107])

  useEffect( () => {

    fetch('/mock-geojson-api.json')
      .then(response => response.json())
      .then( (fetchedFeatures) => {

        // parse fetched geojson into OpenLayers features
        //  use options to convert feature from EPSG:4326 to EPSG:3857
        const wktOptions = {
          dataProjection: 'EPSG:4326',
          featureProjection: 'EPSG:3857'
        }
        const parsedFeatures = new GeoJSON().readFeatures(fetchedFeatures, wktOptions)
        setFeatures(parsedFeatures)

      })

  },[])
  
  return (
    <div className="App">
      <MapWrapper features={features} center={center} />
    </div>
  )
}

export default App