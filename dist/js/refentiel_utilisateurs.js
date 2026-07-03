function deleteUser( el,k ){
  Swal.fire({
    title: l('js-user-delete'),
    showCancelButton: true,
    confirmButtonText: l('js-bouton-supprimer'),
  }).then((result) => {
    if (result.isConfirmed) {
      ajax({methode:"user::delete",id:k},function() {
        $(el).parents('tr:first').remove();
        Swal.fire(
          l('js-user-delete-ok'),
          l('js-user-delete-ok-text'),
          'success'
        )
      })
    }
  })
}



$(document).ready(function() {
  $(document).on('click.editableProfile','#tableUsers .editableProfile',function() {
    changeUserProfile($(this));
  })
})
async function changeUserProfile( el ) {
  let tr = el.parents('tr:first'),
      id_user = tr.attr('data-id'),
      id_profile = tr.attr('data-id-profile');

  const { value: newProfile } = await Swal.fire({
    title: l('js-user-change-profile'),
    input: 'select',
    inputOptions: profiles,
    inputPlaceholder: l('js-user-change-profile-select'),
    showCancelButton: true,
    inputValidator: (value) => {
      return new Promise((resolve) => {
        if (value != id_profile ) {
          resolve()
        } else {
          resolve( l('js-user-change-profile-double') )
        }
      })
    }
  })

  if (newProfile) {
    ajax({
      methode:"securite::changeProfile",
      id_profile : newProfile,
      id_user : id_user
    }, () => {
      el.text(profiles[newProfile])
      info( l('js-user-change-profile-updated'));
    });
  }

}
