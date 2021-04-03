<template>
  <div class="home">
    <NavBar location="" />

    <!-- rest of the page here -->
    <div class="headerMenu">
      <div class="container">
        <h2 class="headerMenu-title">Dashboard</h2>
      </div>
    </div>

    <!-- Content -->
    <div class="container">
      <button type="button" @click="showModal(false, null)" class="btn btn-light">
        New Store
      </button>
      <div class="row mt-2">
        <div class="col-sm-12 col-md-8">
          <table class="table table-hover">
            <thead class="thead-dark">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Store</th>
                <th scope="col">Water Brands</th>
                <th scope="col">Address</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, indexArr) in stores" :key="indexArr">
                <th scope="row">{{ indexArr + 1 }}</th>
                <td>
                  <a :href="'/store/' + item.id">{{ item.name }}</a>
                </td>
                <td>{{ item.water_brands }}</td>
                <td>{{ item.address }}</td>
                <td>
                  <i
                    class="btn btn-outline-info bi bi-pencil-square"
                    @click="showModal(item, indexArr)"
                  ></i>
                  <i
                    @click="deleteStore(item, indexArr)"
                    class="btn btn-outline-danger bi bi-x-circle"
                  ></i>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-sm-12 col-md-4 text-center">
          <canvas id="myChart" width="400" height="400"></canvas>
        </div>
      </div>
    </div>

    <!-- MODAL -->
    <div
      class="modal fade"
      id="formModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="formModal"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ modalTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
              <div class="form-group">
                <label for="store" class="col-form-label">Name:</label>
                <input
                  type="text"
                  class="form-control"
                  id="store"
                  v-model="selectedStore.name"
                />
              </div>
              <div class="form-group">
                <label for="address" class="col-form-label">Address:</label>
                <input
                  type="text"
                  class="form-control"
                  id="address"
                  v-model="selectedStore.address"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              Close
            </button>
            <button type="button" class="btn btn-primary" @click="saveStore">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NavBar from "@/components/NavBar.vue";
import $ from "jquery";
import Chart from "chart.js";
import helper from "@/js/helper";
export default {
  name: "Home",
  components: {
    NavBar,
  },
  data() {
    return {
      selectedStore: {
        name: null,
        address: null,
      },
      modalTitle: "",
      indexArr: null,
      stores: [],
      chart: null,
    };
  },
  methods: {
    showModal(obj, index) {
      this.indexArr = index;
      if (index === null) {
        this.modalTitle = "New Store";
        this.selectedStore = {
          name: null,
          address: null,
        };
      } else {
        this.modalTitle = "Update Store";
        this.selectedStore = {
          id: obj.id,
          name: obj.name,
          address: obj.address,
        };
      }
      $("#formModal").modal("show");
    },
    deleteStore(obj, index) {
      if (!confirm("Are you sure?")) {
        return;
      }

      helper.toggleLoadingScreen(true);
      this.$axios
        .post(`store/delete`, obj)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            helper.showSuccess(result.message);
            this.stores.splice(index, 1);

            // reload chart
            this.loadChart();
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    saveStore() {
      let err = false;
      if (!this.selectedStore.name || this.selectedStore.name.length == 0) {
        helper.showWarning("Please complete Store Name.");
        err = true;
      }
      if (!this.selectedStore.address || this.selectedStore.address.length == 0) {
        helper.showWarning("Please complete Store Address.");
        err = true;
      }

      if (err) {
        return;
      }

      let urlPart = this.indexArr == null ? "add" : "update";

      helper.toggleLoadingScreen(true);
      this.$axios
        .post(`store/${urlPart}`, this.selectedStore)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            helper.showSuccess(result.message);
            if (this.indexArr == null) {
              /** add the new obj */
              this.stores.push(result.data);
            } else {
              /** update the obj */
              this.stores[this.indexArr].name = this.selectedStore.name;
              this.stores[this.indexArr].address = this.selectedStore.address;
            }

            // reload chart
            this.loadChart();
            $("#formModal").modal("hide");
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    loadChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      this.chart = new Chart(document.getElementById("myChart").getContext("2d"), {
        type: "bar",
        options: {
          title: {
            display: true,
            text: "Statistics",
          },
          scales: {
            yAxes: [
              {
                ticks: {
                  beginAtZero: true,
                },
              },
            ],
          },
        },
        data: {
          labels: this.stores.map((store) => store.name),
          datasets: [
            {
              backgroundColor: [
                "#2ecc71",
                "#3498db",
                "#95a5a6",
                "#9b59b6",
                "#f1c40f",
                "#e74c3c",
                "#34495e",
              ],
              label: "My First Dataset",
              data: this.stores.map((store) => parseInt(store.water_brands)),
            },
          ],
        },
      });
    },
    loadStores() {
      helper.toggleLoadingScreen(true);
      this.$axios
        .get(`store/all`)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            this.stores = result.data;
            // load chart
            this.loadChart();
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
  },
  mounted() {
    this.loadStores();
  },
};
</script>
