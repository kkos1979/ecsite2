@extends('layouts.app')

@section('title', '商品修正')

@section('content')
  <h1>商品の修正</h1>
  <div class="base">
    @if ($errors->any())
      <div class="base">
        @foreach ($errors->all() as $error)
          <span class="error">{{ $error }}</span><br>
        @endforeach
      </div>
    @endif
    <form action="/edit/{{ $g->id }}" method="post">
      @csrf
      <p>
        商品名<br>
        <input type="text" name="goods_name" value="{{ old('goods_name') ?? $g->name }}">
      </p>
      <p>
        商品説明<br>
        <textarea name="comment" rows="10" cols="50">{{ old('comment') ?? $g->comment }}</textarea>
      </p>
      <p>
        価格<br>
        <input type="text" name="price" value="{{ old('price') ?? $g->price }}">円
      </p>
      <p>
        在庫<br>
        <input type="text" name="stock" value="{{ old('stock') ?? $g->stock }}">個
      </p>
      <p>
        <input type="submit" value="更新">
      </p>
    </form>
  </div>
  <div class="base">
    <a href="/">一覧に戻る</a>
  </div>
@endsection
