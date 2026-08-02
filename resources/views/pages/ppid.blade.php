@extends('layouts.app')

@section('title', 'Informasi PPID')

@section('metaDescription', 'Dokumen informasi publik Disdikpora Karangasem: informasi berkala, serta merta, dan setiap saat.')

@section('content')
    <x-page-hero title="Informasi PPID" subtitle="Pejabat Pengelola Informasi dan Dokumentasi — informasi publik berkala, serta merta, dan setiap saat" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <livewire:public.ppid-tabs />
    </section>
@endsection
