jQuery(document).ready(function($) {
	/**
	 *
	 * Instructor list filter
	 *
	 * @since  v.1.8.4
	 */
	// Get values on course category selection
	$('[tutor-instructors]').each(function() {
		var $this = $(this);
		var filter_args = {};
		var time_out;

		function run_instructor_filter(name, value, page_number) {
			// Prepare http payload
			var result_container = $this.find('[tutor-instructors-content]');
			var html_cache = result_container.html();
			var attributes = $this.data();
			attributes.current_page = page_number || 1;

			name ? (filter_args[name] = value) : (filter_args = {});
			filter_args.attributes = attributes;
			filter_args.action = 'load_filtered_instructor';

			// Show loading icon
			result_container.html(
				`<div style="text-align:center">
					<div style="background-color: #fff;" class="loading-spinner"></div>
				</div>`
			);

			$.ajax({
				url: window._tutorobject.ajaxurl,
				data: filter_args,
				type: 'POST',
				success: function(r) {
					result_container.html(r);
				},
				error: function() {
					result_container.html(html_cache);
					tutor_toast('Failed', 'Request Error', 'error');
				},
			});
		}

		$this
			.on('change', '[tutor-instructors-category-filter] [type="checkbox"]', function() {
				var values = {};

				$(this)
					.closest('[tutor-instructors-category-filter]')
					.find('input:checked')
					.each(function() {
						values[$(this).val()] = $(this).parent().text();
					});

				var cat_ids = Object.keys(values);
				run_instructor_filter($(this).attr('name'), cat_ids);
			})

			.on('click', '[tutor-instructors-ratings-value]', function(e) {
				const rating = e.target.dataset.value;
				run_instructor_filter('rating_filter', rating);
			})

			.on('change', '#tutor-instructor-relevant-sort', function(e) {
				const short_by = e.target.value;
				run_instructor_filter('short_by', short_by);
			})

			// Get values on search keyword change
			.on('input', '.filter-pc [name="keyword"]', function() {
				var val = $(this).val();
				time_out ? window.clearTimeout(time_out) : 0;
				time_out = window.setTimeout(function() {
					run_instructor_filter('keyword', val);
					time_out = null;
				}, 500);
			})

			.on('click', '[data-page_number]', function(e) {
				// On pagination click
				e.preventDefault();
				run_instructor_filter(null, null, $(this).data('page_number'));
			})

			// Clear filter
			.on('click', '.clear-instructor-filter', function() {
				var $this = $(this).closest('.tutor-instructor-filter');
				$this.find('input[type="checkbox"]').prop('checked', false);
				$this.find('[name="keyword"]').val('');
				const stars = document.querySelectorAll('.tutor-instructor-ratings i');
				//remove star selection
				for (let star of stars) {
					if (star.classList.contains('active')) {
						star.classList.remove('active');
					}
					if (star.classList.contains('tutor-icon-star-bold')) {
						star.classList.remove('tutor-icon-star-bold');
						star.classList.add('tutor-icon-star-line');
					}
				}
				rating_range.innerHTML = ``;
				run_instructor_filter();
			});
	});

	/**
	 * Show start active as per click
	 *
	 * @since v2.0.0
	 */
	const stars = document.querySelectorAll('[tutor-instructors-ratings-value]');
	const rating_range = document.querySelector('.tutor-instructor-rating-filter');
	for (let star of stars) {
		star.onclick = (e) => {
			//remove active if has
			for (let star of stars) {
				if (star.classList.contains('active')) {
					star.classList.remove('active');
				}
				if (star.classList.contains('tutor-icon-star-bold')) {
					star.classList.remove('tutor-icon-star-bold');
					star.classList.add('tutor-icon-star-line');
				}
			}
			//show stars active as click
			const length = e.target.dataset.value;
			for (let i = 0; i < length; i++) {
				stars[i].classList.add('active');
				stars[i].classList.remove('tutor-icon-star-line');
				stars[i].classList.add('tutor-icon-star-bold');
			}
			rating_range.innerHTML = `0.0 - ${length}.0`;
		};
	}
});
