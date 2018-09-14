(function($){

                
    var typeChoice = function( choicesMade ) {
        return {
                    name: 'Typenbezeichnungen',
                    type: 'choice',
                    label: 'Typenbezeichnungen',
                    choices: choicesMade
                };
    };

    var lines = [
        
        {
            name: 'E-Mail',
            type: 'text',
            label: 'Ihre E-Mail-Adresse'
        },
        {
            name: 'Betreff',
            type: 'text',
            label: 'Betreff'
        },
        {
            name: 'Klassifizierung',
            type: 'choice',
            label: 'Klassifizierung',
            choices: [
                {
                    label: 'Problem'
                },
                {
                    label: 'Bestellung',
                    subnodes: [
                        {
                            name: 'Komponente',
                            type: 'choice',
                            label: 'Komponente',
                            choices: [
                                {
                                    label: 'PC',
                                    subnodes: [
                                        typeChoice( [
                                            {
                                                label: 'HP ProDesk 280 G1 MCT (€ 249,99)'
                                            },
                                            {
                                                label: 'Fujitsu ESPRIMO P420 E85 (€ 469,00)'
                                            },
                                            {
                                                label: 'DELL OptiPlex 3020-8828 MT (€ 599,00)'
                                            }
                                        ])
                                    ]
                                },
                                {
                                    label: 'Drucker',
                                    subnodes: [
                                        typeChoice( [
                                            {
                                                label: 'HP LaserJet Pro P1102W (€ 85,20)'
                                            },
                                            {
                                                label: 'Brother MFC J4620DW (€ 126,40)'
                                            },
                                            {
                                                label: 'Samsung SL-C1860fw (€ 343,90)'
                                            }
                                        ] )
                                    ]
                                },
                                {
                                    label: 'Monitor',
                                    subnodes: [
                                        typeChoice( [
                                            {
                                                label: '23“: Acer G236HL (€ 129,00)'
                                            },
                                            {
                                                label: '27“: ASUS PB278Q (€ 339,00)'
                                            },
                                            {
                                                label: '32“: Samsung S32E590C (€ 479,00)'
                                            }
                                        ] )
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    label: 'Sonstiges'
                }
            ]
        },
        {
            name: 'Anfragetext',
            type: 'textarea',
            label: 'Anfragetext'
        },
        {
            name: 'attachments[]',
            type: 'file',
            descrioption: 'Anhänge hinzufügen',
            multiple: 'multiple'
        },
        {
            name: 'submit',
            type: 'submit',
            value: 'Absenden',
            classes: 'btn btn-default'
        }
    ];

    var addLine = function(label, forId, element, parent='#inetWrapper') {
        
        // append element
        if ( element != null ) {
            return $('<div class="line form-group" />').append( $('<label>').attr({
                    for: forId,
            }).addClass('control-label col-3').text( label ? label + ':' : '' ) ).append( $('<div/>').addClass( 'col-9' ).append( element ) ).appendTo(parent);
        } else {
            return $('<div/>');
        }

    };

    var buildStructure = function(parent){
        
        if ( !isNaN(parent) ) { parent = $('#inetWrapper') }

        switch( this.type ) {
            case 'text':
            case 'submit':
            case 'file':
                addLine(this.label, this.name, $('<input/>').attr({
                   name: this.name,
                   id: this.name,
                   type: this.type, 
                   value: this.value || '',
                   multiple: this.multiple,
                   description: this.descrioption,
                }).addClass('form-control').addClass(this.classes), parent).addClass(this.type);
                break;
            case 'textarea':
                addLine(this.label, this.name, $('<textarea/>').attr({
                   name: this.name,
                   id: this.name,
                }).text(this.value), parent);
                break;
            case 'choice':
                
                var newElement = $('<select/>').attr({
                   name: this.name,
                   id: this.name,
                });
                
                addLine(this.label, this.name, newElement, parent );
                
                $(this.choices).each( function(){
                    
                    var choice = this;
                    
                    $('<option/>').text( choice.label ).appendTo( newElement );
                    // go deeper    
                    if ( choice.subnodes ) {
                        var container = $('<div/>').addClass('choice container form-group').appendTo(parent).hide();
                        newElement.change( function() {
                            container.toggle( $(this).val() == choice.label );
                        });
                        
                        $.each(this.subnodes, function(){
                            buildStructure.apply(this, container); }
                        );
                        
                        newElement.change();
                    }
                });
        
                break;
        }
        
    };

    $(function(){
       
       // Go through lines
       $.each(lines, buildStructure );
       
       // Reset on change event
       $('input,textarea,select').change(function(){ $(this).parent().removeClass('has-error').attr('data-error', null); });
       $('input,textarea,select').focus(function(){ $(this).parent().addClass('hide-description'); });
       $('input,textarea,select').blur(function(){ $(this).parent().removeClass('hide-description'); });
       
       $('input[type=file]').each(function(){
          $this = $(this);
          $label = $this.parents('div.line.form-group').find('label');
          $button = $('<label />').attr({ 'for': this.id }).addClass('form-control btn btn-default').text($this.attr('description')).insertBefore($this);
          $this.change(function(){
              $button.attr('data-counter', $this[0].files.length );

              var files = [];
              $.each( $this[0].files, function() { files.push(this.name); } );
              $button.attr('data-content', files.join(', ') );
          });
          $this.change();
       });
       
       // Submit function
       $('#inetWrapper').submit( function(event) {

            var formData = new FormData($('#inetWrapper')[0]);
            $(this).find(/*'input[type=text],select*/'textarea').filter(':visible').each(function(index, item) {
                formData.append(item.name, item.value);
            });

            try {
                $('#loading').show();
                $.ajax({
                    type: 'POST',
                    url:"index.php",
                    data: formData,
                    processData: false,
                    contentType: false
                }).done(function( ){
                    $('#inetWrapper')[0].reset();
                    $('#confirm').show();
                    setTimeout( function() { $('#confirm').hide(); }, 1000 );
                }).fail(function( returnval ){
                    try {
                        data = $.parseJSON( returnval.responseText );
                        if ( data.errors ) {
                            // Handle errors
                            $.each( data.errors, function(key, value) {
                                $('#' + key).parent().addClass('has-error').attr('data-error', value);
                            });
                        } else {
                            $('input,textarea,select').reset();
                        }
                    } catch(e) {}
                    $('#submit-error').show();
                    setTimeout( function() { $('#submit-error').hide(); }, 1000 );
                }).always(function(){
                    $('#loading').hide();
                });
            } catch(e){}
           
           event.preventDefault();
           return false;
       });
    });


})(jQuery);