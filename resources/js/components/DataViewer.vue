<template>
  <div class="row">
    <div class="col-sm-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-1">
              <h4>Search</h4>
            </div>
            <div class="col-md-2">
              <select class="custom-select" v-model="query.search_column">
                <option v-for="(column,index) in columns" :key="index" :value="column">{{ column }}</option>
              </select>
            </div>
            <div class="col-md-2">
              <select class="custom-select" v-model="query.search_operator">
                <option v-for="(val, key) in operators" :key="key" :value="key">{{val}}</option>
              </select>
            </div>
            <div class="col-md-7">
              <div class="input-group mb-3">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search"
                  aria-label="search"
                  aria-describedby="basic-addon2"
                  v-model="query.search_input"
                  @keyup.enter="fetchIndexData()"
                />
                <div class="input-group-append">
                  <button
                    class="btn btn-outline-secondary"
                    type="button"
                    @click="fetchIndexData()"
                  >Search</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th
                    scope="col"
                    v-for="(column,index) in columns"
                    :key="index"
                    @click="toogleOrder(column)"
                  >
                    <span>{{column}}</span>
                    <span v-if="column === query.column">
                      <span v-if="query.direction === 'desc'">&uarr;</span>
                      <span v-else>&darr;</span>
                    </span>
                  </th>
                  <th scope="col">
                    <span>Action</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row,index) in model.data" :key="index">
                  <td v-for="(val, key) in row" v-if="matchingColumns(key)" :key="key">{{val}}</td>
                  <td v-if="hasAction">
                    <button class="btn btn-sm btn-primary" v-if="canEdit">Edit</button>
                    <button
                      class="btn btn-sm btn-danger"
                      v-if="canDelete"
                      @click="confirmDelete(row.id)"
                    >Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-md-6">
              <div class="row">
                <div class="col-md-6">
                  <span>Displaying {{model.from}} - {{model.to}} of {{model.total}} rows</span>
                </div>
              </div>
              <div class="d-flex flex-row">
                <span class="mr-1">Rows per page</span>
                <input
                  type="text"
                  v-model="query.per_page"
                  class="form-control form-control-sm"
                  style="max-width: 10%"
                  @keyup.enter="fetchIndexData()"
                />
              </div>
            </div>
            <div class="col-md-6">
              <nav class="float-right">
                <ul class="pagination pagination-sm">
                  <li class="page-item">
                    <button
                      class="page-link"
                      aria-label="Previous"
                      @click="prev()"
                      :disabled="!model.prev_page_url"
                    >
                      <span aria-hidden="true">&laquo;</span>
                      <span class="sr-only">Previous</span>
                    </button>
                  </li>
                  <li class="page-item" v-for="page in model.last_page" :key="page">
                    <button class="page-link" @click="goToPage(page)">{{page}}</button>
                  </li>
                  <li class="page-item">
                    <button
                      class="page-link"
                      aria-label="Next"
                      @click="next()"
                      :disabled="!model.next_page_url"
                    >
                      <span aria-hidden="true">&raquo;</span>
                      <span class="sr-only">Next</span>
                    </button>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["source", "column", "canEdit", "canDelete", "hasAction"],
  data() {
    return {
      model: {},
      columns: {},
      query: {
        page: 1,
        column: this.column,
        direction: "desc",
        per_page: 10,
        search_column: this.column,
        search_operator: "equal",
        search_input: "",
      },
      operators: {
        equal: "=",
        not_equal: "!=",
        less_than: "<",
        greather_than: ">",
        less_than_or_equal_to: "<=",
        greater_than_or_equal_to: ">=",
        in: "IN",
        like: "LIKE",
      },
    };
  },
  created() {
    this.fetchIndexData();
    console.log(this.canEdit, this.canDelete);
  },
  methods: {
    next() {
      if (this.model.next_page_url) {
        this.query.page++;
        this.fetchIndexData();
      }
    },
    prev() {
      if (this.model.prev_page_url) {
        this.query.page--;
        this.fetchIndexData();
      }
    },
    goToPage(page) {
      this.query.page = page;
      this.fetchIndexData();
    },
    toogleOrder(column) {
      if (column === this.query.column) {
        // only change direction
        if (this.query.direction === "desc") {
          this.query.direction = "asc";
        } else {
          this.query.direction = "desc";
        }
      } else {
        this.query.column = column;
        this.query.direction = "asc";
      }
      this.fetchIndexData();
    },
    fetchIndexData() {
      this.$Progress.start();
      if (this.query.per_page == "") this.query.per_page = 10;
      let endpoint = `${BASE_URL}/${this.source}?column=${this.query.column}&direction=${this.query.direction}&page=${this.query.page}&per_page=${this.query.per_page}&search_column=${this.query.search_column}&search_operator=${this.query.search_operator}&search_input=${this.query.search_input}`;
      axios
        .get(endpoint)
        .then((response) => {
          if (response.status === 200) {
            this.model = response.data.model;
            this.columns = response.data.columns;
            this.$Progress.finish();
          }
        })
        .catch((response) => {
          console.log(response);
          this.$Progress.fail();
        });
    },
    matchingColumns(key) {
      return this.columns.includes(key);
    },
    confirmDelete(id) {
      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Anda tidak akan dapat mengembalikan ini!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Delete",
      }).then((result) => {
        if (result.value) {
          this.delete(id);
        }
      });
    },
    async delete(id) {
      this.$Progress.start();
      try {
        let endpoint = `${BASE_URL}/${this.source}/${id}`;
        let response = await axios.delete(endpoint);

        if (response.status == 200) {
          Swal.fire("Berhasil!", "Data berhasil dihapus.", "success");
          this.fetchIndexData();
          this.$Progress.finish();
        }
      } catch (error) {
        console.log(error);
        Swal.fire("Gagal!", "Data gagal dihapus.", "error");
        this.$Progress.fail();
      }
    },
  },
};
</script>

<style>
</style>
