// react
import React, { useState, useEffect, useRef } from 'react';

// openlayers
import Map from 'ol/Map'
import View from 'ol/View'
import TileLayer from 'ol/layer/Tile'
import VectorLayer from 'ol/layer/Vector'
import VectorSource from 'ol/source/Vector'
import * as OSMSource from 'ol/source'
import XYZ from 'ol/source/XYZ'
import {transform} from 'ol/proj'
import {toStringXY} from 'ol/coordinate';

import { fromLonLat, get } from "ol/proj";

function  MapWrapper(props) {
  const [ map, setMap ] = useState()
  const [ featuresLayer, setFeaturesLayer ] = useState()
  const [ selectedCoord , setSelectedCoord ] = useState()
  const mapElement = useRef()
  const mapRef = useRef()
  mapRef.current = map
  useEffect( () => {
    const initalFeaturesLayer = new VectorLayer({
      source: new VectorSource()
    })
    console.log(props.center)
    const initialMap = new Map({
      target: mapElement.current,
      layers: [
       new TileLayer({
          source: new OSMSource.OSM()
        }),
        initalFeaturesLayer
      ],
      view: new View({
        projection: 'EPSG:3857',
        center: fromLonLat([-121.955238,37.354107]),
        zoom: 16
      }),
      controls: []
    })
    initialMap.on('click', handleMapClick)
    setMap(initialMap)
    setFeaturesLayer(initalFeaturesLayer)
  },[])

  useEffect( () => {
    if (props.features.length) {
      featuresLayer.setSource(
        new VectorSource({
          features: props.features // make sure features is an array
        })
      )
      map.getView().fit(featuresLayer.getSource().getExtent(), {
        padding: [100,100,100,100]
      })
    }
  },[props.features])

  // map click handler
  const handleMapClick = (event) => {
    const clickedCoord = mapRef.current.getCoordinateFromPixel(event.pixel);
    const transormedCoord = transform(clickedCoord, 'EPSG:3857', 'EPSG:4326')
    setSelectedCoord( transormedCoord )
  }
  return (      
    <div>
      <div ref={mapElement} className="map-container"></div>
      <div className="clicked-coord-label">
        <p>{ (selectedCoord) ? toStringXY(selectedCoord, 5) : '' }</p>
      </div>
    </div>
  ) 
}
export default MapWrapper