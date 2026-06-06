<x-layouts.app title="Joudah Store - Collection" description="Discover the full Joudah Store collection.">
    <x-hero />

    <x-catalog-section :categories="$categories" />
    <x-shorts-section :videos="$videos" />
    <x-about-section />
    <x-footer />
</x-layouts.app>
