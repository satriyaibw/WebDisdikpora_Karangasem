@extends('layouts.app')

@section('title', 'Layanan Publik')

@section('metaDescription', 'Katalog layanan publik Disdikpora Karangasem beserta persyaratan, alur prosedur, dan estimasi waktu pelayanan.')

@section('content')
    <x-page-hero title="Katalog Layanan Publik" subtitle="Layanan administrasi Disdikpora Karangasem — cari berdasarkan kata kunci atau bidang" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <livewire:public.service-catalog />
    </section>
@endsection
