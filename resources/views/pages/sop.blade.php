@extends('layouts.app')

@section('title', 'Dokumen SOP')

@section('metaDescription', 'Kumpulan Standar Operasional Prosedur (SOP) pelayanan Disdikpora Karangasem dengan pratinjau PDF.')

@section('content')
    <x-page-hero title="Dokumen SOP" subtitle="Standar Operasional Prosedur pelayanan — filter berdasarkan bidang dan pratinjau langsung" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h2 class="sr-only">Katalog Dokumen SOP</h2>
        <livewire:public.sop-catalog />
    </section>
@endsection
