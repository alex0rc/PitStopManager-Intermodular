import {
  AfterViewInit,
  Component,
  ElementRef,
  EventEmitter,
  Input,
  OnChanges,
  OnDestroy,
  Output,
  SimpleChanges,
  ViewChild,
} from '@angular/core';
import * as L from 'leaflet';

export interface MapCoordinates {
  latitude: number;
  longitude: number;
}

export interface MapCenter {
  latitude: number;
  longitude: number;
  zoom?: number;
}

@Component({
  selector: 'app-map-picker',
  standalone: true,
  templateUrl: './map-picker.component.html',
  styleUrl: './map-picker.component.scss',
})
export class MapPickerComponent implements AfterViewInit, OnChanges, OnDestroy {
  @ViewChild('mapHost', { static: true }) mapHost!: ElementRef<HTMLDivElement>;

  @Input() latitude: number | null = null;
  @Input() longitude: number | null = null;
  @Input() center: MapCenter | null = null;
  @Input() height = '360px';
  @Input() zoom = 6;

  @Output() coordinatesChange = new EventEmitter<MapCoordinates>();

  private map?: L.Map;
  private marker?: L.Marker;
  private ready = false;

  ngAfterViewInit(): void {
    this.initMap();
    this.ready = true;
    this.syncFromInputs();
  }

  ngOnChanges(changes: SimpleChanges): void {
    if (!this.ready || !this.map) return;

    if (changes['center'] && this.center) {
      this.placeMarker(this.center.latitude, this.center.longitude, this.center.zoom ?? 14, false);
      return;
    }

    if (changes['latitude'] || changes['longitude']) {
      this.syncFromInputs();
    }
  }

  ngOnDestroy(): void {
    this.map?.remove();
    this.map = undefined;
    this.marker = undefined;
  }

  private initMap(): void {
    const defaultLat = 40.4168;
    const defaultLng = -3.7038;

    this.map = L.map(this.mapHost.nativeElement, {
      center: [defaultLat, defaultLng],
      zoom: this.zoom,
      scrollWheelZoom: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(this.map);

    this.map.on('click', (e: L.LeafletMouseEvent) => {
      this.placeMarker(e.latlng.lat, e.latlng.lng);
    });

    setTimeout(() => this.map?.invalidateSize(), 0);
  }

  private syncFromInputs(): void {
    if (this.latitude != null && this.longitude != null) {
      this.placeMarker(this.latitude, this.longitude, this.zoom, false);
    }
  }

  private placeMarker(lat: number, lng: number, zoom?: number, emit = true): void {
    if (!this.map) return;

    const icon = L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      shadowSize: [41, 41],
    });

    if (this.marker) {
      this.marker.setLatLng([lat, lng]);
    } else {
      this.marker = L.marker([lat, lng], { icon, draggable: true }).addTo(this.map);
      this.marker.on('dragend', () => {
        const pos = this.marker!.getLatLng();
        this.emitCoords(pos.lat, pos.lng);
      });
    }

    this.map.setView([lat, lng], zoom ?? this.map.getZoom());
    if (emit) {
      this.emitCoords(lat, lng);
    }
  }

  private emitCoords(lat: number, lng: number): void {
    this.coordinatesChange.emit({
      latitude: Math.round(lat * 1_000_000) / 1_000_000,
      longitude: Math.round(lng * 1_000_000) / 1_000_000,
    });
  }
}
