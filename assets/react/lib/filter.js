/**
 * On click add filter value on the url
 * and refresh page
 *
 * Handle bulk action
 *
 * @package Filter / sorting
 * @since v2.0.0
 */
window.onload = () => {
  document.getElementById("tutor-backend-filter-course").onchange = (e) => {
    window.location = urlPrams("course-id", e.target.value);
  };
  document.getElementById("tutor-backend-filter-order").onchange = (e) => {
    window.location = urlPrams("order", e.target.value);
  };
  document.getElementById("tutor-backend-filter-date").onchange = (e) => {
    window.location = urlPrams("date", e.target.value);
  };
  document.getElementById("tutor-admin-search-filter-form").onsubmit = (e) => {
    e.preventDefault();
    const search = document.getElementById("tutor-backend-filter-search").value;
    window.location = urlPrams("search", search);
  };

  /**
   * Onsubmit bulk form handle ajax request then reload page
   */
  const bulkForm = document.getElementById("tutor-admin-bulk-action-form");
  bulkForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(bulkForm);
    formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
    try {
      const post = await fetch(window._tutorobject.ajaxurl, {
        method: "POST",
        body: formData,
      });
      const response = await post.json();
      console.log(response);
    } catch (error) {
      alert(error);
    }
    console.log(formData.get("bulk-action"));
  };

  function urlPrams(type, val) {
    var url = new URL(window.location.href);
    var search_params = url.searchParams;
    search_params.set(type, val);

    url.search = search_params.toString();

    search_params.set("paged", 1);
    url.search = search_params.toString();

    return url.toString();
  }
};
