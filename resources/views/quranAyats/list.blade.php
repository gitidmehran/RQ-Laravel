@extends('./layout/layout')
@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-11 col-lg-11">
                <div class="card card-sm">
                    <div class="card-body row no-gutters align-items-center">
                        <div class="col-auto">
                            <i class="fas fa-search h4 text-body"></i>
                        </div>
                        <div class="col">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <select class="form-control form-control-lg per-page" id="inputGroupSelect02">
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>
                                <input class="form-control form-control-lg form-control-borderless search" type="search"
                                    placeholder="آپ کیا پڑھنا چاہتے ہیں؟" dir="rtl">
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-11 col-lg-11">
                <div class="loader bg-white text-center pt-5 d-none" style="height:200px">
                    <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
                </div>
                <div class="list"></div>
                <div class="mt-3 page-div d-none">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-end  pagination-lg">
                        </ul>
                    </nav>
                </div>
            </div>


        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            const siteUrl = $("#site-url").text();

            const strHighlightText = (string, str_to_highlight) => {
                var reg = new RegExp(str_to_highlight, 'gi');
                return string.replace(reg, function(str) {
                    return '<span style="color:green;"><b>' + str + '</b></span>'
                });
            }
            $(document).on('keyup', '.search', function() {
                const val = $(this).val();
                const perpage = $('.per-page').val();
                const page = 1;
                if (val !== "") {
                    renderList(val, perpage, page)
                }
            });

            $(document).on('click', '.page-link', function() {
                console.log('click working')
                const parentList = $(this).parent();
                const pageNumber = $(this).attr('data-value');
                const val = $('.search').val();
                const perpage = $('.per-page').val();
                if (val !== "") {
                    renderList(val, perpage, pageNumber)
                }
                $('.active').removeClass('active');
                $(parentList).addClass('active')
            })

            $(document).on('change', '#inputGroupSelect02', function() {
                console.log('per page change')
                const perpage = $(this).val();
                const pageNumber = 1;
                const val = $('.search').val();
                if (val !== "") {
                    renderList(val, perpage, pageNumber)
                }
                $('.active').removeClass('active');
                $(parentList).addClass('active')
            })

            const renderList = async (val, perpage, page) => {
                $('.loader').removeClass('d-none');
                $('.list').html('')
                await $.ajax({
                    type: 'POST',
                    url: `{{ url('/filter-query?page=${page}') }}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'query_data': val,
                        'perpage': perpage,
                    },
                    success: function(response) {
                        $('.loader').addClass('d-none');
                        let html = '';
                        const {
                            list
                        } = response;
                        if (list.data.length > 0) {
                            list.data.forEach(item => {
                                const highlightedStr = strHighlightText(item.arabic,
                                    val);
                                html += `
                                <a href='${siteUrl}/detail/${item.id}' class="text-decoration-none" target="_blank">
                                    <div class="card card-sm mt-2">
                                        <div class="card-body row no-gutters align-items-center pt-3">
                                            <blockquote class="blockquote" style="text-align:right !important">
                                                <p class="mb-0">${highlightedStr}</p>
                                            </blockquote>
                                        </div>
                                    </div>
                                </a>
                            `;
                            })

                        }
                        $(".list").html(html);
                        renderPagintion(list.links);
                    }
                });
            }
            const renderPagintion = (links) => {
                let page = '';
                if (links.length > 0) {
                    const newLinks = links.filter(link => !link.label.includes('&'))
                    newLinks.forEach(link => {
                        page +=
                            `<li class="page-item ${link.active===true?'active':''}"><a class="page-link" href="#" data-value="${link.label}">${link.label}</a></li>`
                    })
                    $('.page-div').removeClass('d-none');
                } else {
                    $('.page-div').addClass('d-none')
                }
                $('.pagination-lg').html(page)
            }
        })
    </script>
@endsection
