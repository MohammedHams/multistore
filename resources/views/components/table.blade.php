<div  id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
    <table  {!! $attributes->merge(['class' => 'table table-striped border-top border-start border-bottom gy-2 gy-5 gs-7 data-table dataTable no-footer']) !!} >
        <!--begin::Table head-->
        <thead>
        <!--begin::Table row-->
        <tr class="text-start fw-bolder fs-7 text-uppercase">
            {{$thead}}
        </tr>
        <!--end::Table row-->
        </thead>
        <!--end::Table head-->
        <!--begin::Table body-->
        <tbody>
        {{$tbody}}
        </tbody>
        <!--end::Table body-->
    </table>
</div>
