<script lang="ts">
  import { ImageGallery } from './index.js';
  
  export let Hst: any;
  
  const sampleImages = [
    'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=400',
    'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400',
    'https://images.unsplash.com/photo-1682695794816-7b9da0f8f4f0?w=400',
    'https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?w=400',
    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
    'https://images.unsplash.com/photo-1511884642898-4c92249e20b6?w=400',
    'https://images.unsplash.com/photo-1501594907352-04cda38ebc29?w=400',
    'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?w=400',
    'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=400',
  ];
  
  const fewImages = sampleImages.slice(0, 4);
  const manyImages = sampleImages;
  
  function handleImageClick(index: number) {
    console.log('Image clicked:', index);
  }
</script>

<Hst.Story title="Media/ImageGallery" icon="lucide:images">
  <Hst.Variant title="Default (3 Columns)">
    <div class="p-4">
      <ImageGallery images={sampleImages} onImageClick={handleImageClick} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="2 Columns">
    <div class="p-4">
      <ImageGallery images={manyImages} columns={2} onImageClick={handleImageClick} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="4 Columns">
    <div class="p-4">
      <ImageGallery images={manyImages} columns={4} onImageClick={handleImageClick} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Small Gallery">
    <div class="p-4">
      <div class="max-w-2xl mx-auto">
        <ImageGallery images={fewImages} columns={2} onImageClick={handleImageClick} />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Large Gap">
    <div class="p-4">
      <ImageGallery images={sampleImages} gap={8} onImageClick={handleImageClick} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Small Gap">
    <div class="p-4">
      <ImageGallery images={sampleImages} gap={2} onImageClick={handleImageClick} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="In a Card">
    <div class="flex justify-center p-4">
      <div class="w-full max-w-3xl rounded-lg border bg-card p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Photo Gallery</h3>
        <ImageGallery images={sampleImages} onImageClick={handleImageClick} />
        <p class="mt-4 text-sm text-muted-foreground">
          Click any image to view in full screen
        </p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Responsive Grid">
    <div class="p-4">
      <div class="w-full">
        <ImageGallery images={manyImages} columns={3} onImageClick={handleImageClick} />
        <p class="mt-4 text-sm text-muted-foreground text-center">
          Use arrow keys to navigate in lightbox
        </p>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
