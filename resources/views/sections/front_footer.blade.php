<footer class="site-footer">
    <div class="container">
        <div class="row gap-y align-items-center">
            <div class="col-12 col-lg-3">
                <p class="text-center text-lg-left">
                    <a href="{{ route('front.home') }}">
                        @if(is_null($setting->logo))
                            <img src="{{ asset('front/img/worksuite-logo.png') }}" alt="home" />
                        @else
                            <img src="{{ asset('user-uploads/app-logo/'.$setting->logo) }}" alt="home" />
                        @endif
                    </a>
                </p>
            </div>

            <div class="col-12 col-lg-6">
                @php $routeName = request()->route()->getName(); @endphp
                <ul class="nav nav-primary nav-hero">
                    @forelse($footerSettings as $footerSetting)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('front.page', $footerSetting->slug) }}" >{{ $footerSetting->name }}</a>
                    </li>
                    @empty

                    @endforelse
                </ul>
                {{--<ul class="nav nav-primary nav-hero">--}}
                    {{--<li class="nav-item">--}}
                        {{--<a class="nav-link" @if($routeName != 'front.home') href="{{route('front.home').'#home'}}" @else data-scrollto="home" @endif >@lang('app.menu.home')</a>--}}
                    {{--</li>--}}
                    {{--<li class="nav-item">--}}
                        {{--<a class="nav-link" @if($routeName != 'front.home') href="{{route('front.home').'#section-features'}}" @else  data-scrollto="section-features" @endif>@lang('app.menu.features')</a>--}}
                    {{--</li>--}}
                    {{--<li class="nav-item hidden-sm-down">--}}
                        {{--<a class="nav-link" @if($routeName != 'front.home') href="{{route('front.home').'#section-pricing'}}" @else  data-scrollto="section-pricing" @endif>@lang('app.menu.pricing')</a>--}}
                    {{--</li>--}}
                    {{--<li class="nav-item hidden-sm-down">--}}
                        {{--<a class="nav-link" @if($routeName != 'front.home') href="{{route('front.home').'#section-contact'}}" @else data-scrollto="section-contact" @endif>@lang('app.menu.contact')</a>--}}
                    {{--</li>--}}
                {{--</ul>--}}
            </div>

            <div class="col-12 col-lg-3">
                <div class="social text-center text-lg-right">
                    @foreach (json_decode($detail->social_links,true) as $link)
                        @if (strlen($link['link']) > 0)
                            <a class="social-{{$link['name']}}" href="{{ $link['link'] }}" target="_blank">
                                <i class="fab fa-{{$link['name']}}@if ($link['name'] == 'facebook')-f @endif"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>