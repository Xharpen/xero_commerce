{{\App\Facades\XeFrontend::js('https://cdn.jsdelivr.net/npm/vue/dist/vue.js')->load()}}

<div id="option">
    {{-- 커스텀 옵션 --}}
    @if($customOptions->count() > 0)
        @foreach($customOptions as $i => $option)
        <div>{{ $option->description }}</div>
        <div class="box-option">
            <strong>{{ $option->name }} {{ $option->is_required ? '(필수)' : '' }}</strong>
            {!! $option->renderValueInput([
                'class' => 'form-select',
                'v-model' => "customOptions[$i]['value']",
                'v-on:change' => "customOptions[$i]['display_value'] = \$event.target.options ? \$event.target.selectedOptions[0].text : \$event.target.value"
            ]); !!}
            <input type="hidden" v-model="customOptions[{{$i}}]['name']" value="{{$option->name}}" />
            <input type="hidden" v-model="customOptions[{{$i}}]['type']" value="{{$option->type}}" />
        </div>
        @endforeach
    @endif
    {{-- 기본값(옵션품목1개), 조합일체형, 단독형 --}}
    @if($optionType == \Xpressengine\Plugins\XeroCommerce\Models\Product::OPTION_TYPE_COMBINATION_MERGE || $optionType == \Xpressengine\Plugins\XeroCommerce\Models\Product::OPTION_TYPE_SIMPLE)
    <div class="box-option">
        <strong>옵션 선택</strong>
        <select v-model="selectedVariant" class="form-select">
            <option selected :value="undefined">[필수] 옵션을 선택해주세요</option>
            <option v-for="item in variants" :value="item" :disabled="item.state_deal!=='판매중'">@{{item.name}}
                (+@{{Number(item.additional_price).toLocaleString()}} ) @{{(item.state_deal!=='판매중')? '-'+ item.state_deal: ''}}
            </option>
        </select>
    </div>
    @endif
    {{--  조합분리형  --}}
    @if($optionType == \Xpressengine\Plugins\XeroCommerce\Models\Product::OPTION_TYPE_COMBINATION_SPLIT)
    <div v-for="(option, i) in options" class="box-option">
        <strong>@{{ option.name }}</strong>
        <select v-model="selectedOptions[i]" class="form-select">
            <option selected :value="undefined">[필수] 옵션을 선택해주세요</option>
            <option v-for="value in option.values" :value="{[option.name] : value}">@{{value}}</option>
        </select>
    </div>
    @endif
    <div class="product-info-counter">
        <div v-if="!hasOnlyOneItem" class="product-info-cell" v-for="(selectedOption, key) in select">
            <div class="product-info-counter-title">@{{selectedOption.variant.name}} </div>
            <div v-for="(v, k) in selectedOption.custom_options" style="padding-left: 10px">@{{ k }} : @{{ v }}</div>
            <div class="xe-spin-box">
                <button type="button" @click="selectedOption.count--; if(selectedOption.count<=0)dropOption(key)"><i class="xi-minus-thin"></i><span class="xe-sr-only">감소</span></button>
                <p>@{{selectedOption.count}}</p>
                <button type="button" @click="selectedOption.count++"><i class="xi-plus-thin"></i><span class="xe-sr-only">증가</span></button>
            </div>
            <p class="product-info-counter-sum">@{{(selectedOption.variant.sell_price * selectedOption.count).toLocaleString()}}원</p>
            <button class="xe-btn xe-btn-remove" @click="dropOption(key)"><i class="xi-close-thin"></i><span class="xe-sr-only">이 옵션 삭제</span></button>
        </div> <!-- //product-info-cell -->

        <div v-if="hasOnlyOneItem" class="product-info-cell" v-for="(selectedOption, key) in select">
            <div class="product-info-counter-title">@{{selectedOption.variant.name}} </div>
            <div v-for="option in selectedOption.custom_options" style="padding-left: 10px">@{{ option.name }} : @{{ option.display_value }}</div>
            <div class="xe-spin-box">
                <button type="button" @click="(selectedOption.count>1) ? selectedOption.count-- : ''"><i class="xi-minus-thin"></i><span class="xe-sr-only">감소</span></button>
                <p>@{{selectedOption.count}}</p>
                <button type="button" @click="selectedOption.count++"><i class="xi-plus-thin"></i><span class="xe-sr-only">증가</span></button>
            </div>
            <p class="product-info-counter-sum">@{{(selectedOption.variant.sell_price * selectedOption.count).toLocaleString()}}원</p>
        </div>
    </div> <!-- //product-info-counter -->

    <p class="price-sum"><span class="text">총 상품금액</span> @{{totalChoosePrice.toLocaleString()}}<i>원</i></p>
    <input type="hidden" v-model="chooseJson" name="choose">
</div>
<script>
    $(function(){
        Vue.config.devtools = true;
        new Vue({
            el: "#option",
            name: "OptionSelectComponent",
            watch: {
                // 조합분리형 & 단독형을 위한 함수
                selectedOptions(selectedOptions) {
                    // 모든 옵션이 선택되었다면
                    if(selectedOptions.length == this.options.length) {
                        // 옵션품목중 일치하는 조건으로 가져옴
                        let variant = this.variants.find(item => {
                            let selectedCombination = selectedOptions.reduce((obj, item) => ({...obj, ...item}), {});
                            return JSON.stringify(item.combination_values) == JSON.stringify(selectedCombination);
                        });
                        // 일치하는 품목이 있으면
                        if(variant) {
                            this.addVariantToList(variant);
                        }
                    }
                },
                selectedVariant (el) {
                    return this.addVariantToList(el);
                },
                reset: function () {
                    this.select= [];
                    this.initialize();
                }
            },
            computed: {
                totalChoosePrice: function() {
                    return this.sum(this.select)
                },
                hasOnlyOneItem: function () {
                    return this.variants.length ===1
                },
                chooseJson: function () {
                    return JSON.stringify(this.select)
                }
            },
            data: function() {
                return {
                    selectedOptions: [],
                    selectedVariant: undefined,
                    select: [],
                    pay: 'prepay',
                    options: {!! json_encode($options) !!},
                    variants: {!! json_encode($variants) !!},
                    customOptions: {!! json_encode($customOptions) !!},
                    alreadyChoose:{!! json_encode($choose) !!},
                    reset:null
                }
            },
            methods: {
                sum: function (array) {
                    return array.map(function (v) {
                        return v.variant.sell_price * v.count
                    }).reduce(function (a, b) { return a + b }, 0);
                },
                dropOption: function (key) {
                    this.select.splice(key, 1)
                },
                toggleButton: function () {
                    if($("#toggleBtn").hasClass("on")){
                        $("#toggleBtn").removeClass("on")
                    }else{
                        $("#toggleBtn").addClass("on")
                    }
                },
                initialize: function () {
                    // 커스텀 옵션이 있는경우, 상품이 하나여도 옵션이 달라질수 있기 때문에 아래 코드 주석처리
                    // if(this.hasOnlyOneItem && this.select.length===0) this.selectedVariant=this.variants[0];
                },
                // 선택된 옵션목록에 아이템 추가
                addVariantToList(el) {
                    if (el == undefined) return
                    if (!this.validateCustomOption()) {
                        alert('필수옵션을 입력해야 합니다');
                        this.selectedVariant = undefined;
                        return
                    }
                    let exist = this.select.find((v) => {
                        // 커스텀옵션이 설정되어 있다면
                        if(Object.values(this.customOptions).filter(a => a != '').length > 0) {
                            let isOptionMatch = true;
                            if(v.custom_options) {
                                let values1 = v.custom_options.map(option => option.value);
                                let values2 = this.customOptions.map(option => option.value);
                                isOptionMatch = JSON.stringify(values1) == JSON.stringify(values2);
                            }
                            return v.variant.id === el.id && isOptionMatch
                        }
                        return v.variant.id === el.id
                    });
                    if (exist) {
                        exist.count++
                    } else {
                        this.select.push({
                            id: null,
                            variant: el,
                            count: 1,
                            custom_options: JSON.parse(JSON.stringify(this.customOptions))
                        })
                    }
                    this.$emit('input', this.select)
                    this.selectedVariant = undefined;
                    this.selectedOptions = [];
                    this.customOptions.map(function(option) {
                        delete option.value
                    })
                },
                validateCustomOption() {
                    let invalids = this.customOptions.filter(option => {
                        // 필수옵션인데 값이 없는 경우 true
                        if(option.is_required) {
                            return !option.value;
                        }
                        return false
                    })
                    // invalid가 하나도 없으면 true
                    return !invalids.length;
                }
            },
            mounted: function () {
                this.select = this.alreadyChoose
                this.$emit('input', this.select)
                this.initialize()
            }
        })
    })
</script>
