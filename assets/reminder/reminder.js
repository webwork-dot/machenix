window.addEventListener("DOMContentLoaded", () => {
    const nc = new NotificationCenter();
});

class NotificationCenter {
      constructor() {
        this.items = [];
        this.itemsToKill = [];
        this.messages = []; // Initialize messages as an empty array
        this.killTimeout = null;
        this.init();
    }

    async init() {
        try {
            // Fetch notification messages using an asynchronous request
            this.messages = await this.fetchNotificationMessages();
			var count= this.messages.length;
			let spawnCount = (count > 10) ? 10 : count;
            this.spawnNotes(spawnCount,this.messages);
        } catch (error) {
            console.error('Error fetching notification messages:', error);
        }
    }

    async fetchNotificationMessages() {
        return new Promise((resolve, reject) => {
            $.ajax({
                 url: base_url+'common/get_ajax_reminder_list',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
					
                    resolve(data); // Resolve the promise with the fetched data
                },
                error: function(xhr, status, error) {
                    reject(error); // Reject the promise with the error message
                }
            });
        });
    }
   	
	async spawnNote(arr) {
        $(".loader").fadeOut("slow");	
		return new Promise((resolve, reject) => {
			setTimeout(() => {
				const message = arr;
				const note = new Notification({
					id: message.id,
					icon: message.icon,
					title: message.title,
					reminder_date: message.reminder_date,
					subtitle: message.subtitle,
					actions: message.actions
				});
				const transY = 100 * this.items.length;

				note.el.style.transform = `translateY(${transY}%)`;
				note.el.addEventListener("click", this.killNote.bind(this, note.id));
			
			
				this.items.push(note);
				resolve(200);	
			}, 1000);
			
		});
    }
	
    async spawnNotes(amount,arr) {
        let count = typeof amount === "number" ? amount : this.random(1, 1, true);
        let delay = 0;
		
		for (let i = 0; i < arr.length; i++) {
			var p_res = await this.spawnNote(arr[i]);
			if (p_res == 200) {
				
			} else {
				break;
			}
		}
	}
		
	killNote(id, e) {
    const noteIndex = this.items.findIndex(item => item.id === id);

    if (noteIndex !== -1) {
        const note = this.items[noteIndex];

        // Display a confirmation dialog using duDialog
        const confirmDlg = duDialog(null, "Are you sure you want to dismiss this notification?", {
            init: true,
            dark: false,
            buttons: duDialog.OK_CANCEL,
            okText: 'Proceed',
            callbacks: {
			  okClick: function(e) {
				$(".dlg-actions").find("button").attr("disabled",true);
				$(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
				$(".loader").show();  
				$.ajax({
				  type: 'POST',
				  url: base_url + 'common/action_reminder_done',
				  dataType: 'json', 
				  data: {id:id},
				})
				.done(function(res) {
				  confirmDlg.hide();
				  if (res.status == '200') {
					$(".loader").hide(); 
					 Swal.fire({
						title: "Success!",
						text: res.message,
						icon: "success",
						customClass: {
							confirmButton: "btn btn-primary"
						},
						buttonsStyling: !1
					  }).then(() => { 
					   $("#reminder_noti #"+id).hide();
					  this.updateList(res.data, noteIndex);}); 
					
				  } else {
					  $(".loader").hide(); 
						Swal.fire({
							title: "Error!",
							text: res.message ,
							icon: "error",
							customClass: {
								confirmButton: "btn btn-primary"
							},
							buttonsStyling: !1
						})  
				  }
				})
				.fail(function(response) {
					$(".loader").fadeOut("slow");  
					Swal.fire({
						title: "Error!",
						text: res.message ,
						icon: "error",
						customClass: {
							confirmButton: "btn btn-primary"
						},
						buttonsStyling: !1
					})
				});
			  }
            }
        });
        confirmDlg.show();
    }
}	

    updateList(updatedData, noteIndex) {
        const newMessages = updatedData.data;
        this.messages = newMessages;

        if (noteIndex !== -1) {
            const noteToRemove = this.items[noteIndex];
            document.body.removeChild(noteToRemove.el);
            this.items.splice(noteIndex, 1);
        }

        //this.spawnNote();
    }

    shiftNotes() {
        this.items.forEach((item, i) => {
            const transY = 100 * i;
            item.el.style.transform = `translateY(${transY}%)`;
        });
    }

 
    random(min, max, round = false) {
        const percent = crypto.getRandomValues(new Uint32Array(1))[0] / 2**32;
        const relativeValue = (max - min) * percent;

        return min + (round === true ? Math.round(relativeValue) : +relativeValue.toFixed(2));
    }
}

class Notification {
	constructor(args) {
		this.args = args;
		this.el = null;
		this.id = null;
		this.killTime = 300;
		this.init(args);
	}
	init(args) {		
		const {id,icon,title,subtitle,reminder_date,actions} = args;
		const block = "notification";
		const parent = document.getElementById('reminder_noti');
		const xmlnsSVG = "http://www.w3.org/2000/svg";
		const xmlnsUse = "http://www.w3.org/1999/xlink";

		const note = this.newEl("div");
		note.id = id;
		note.className = block;
		parent.insertBefore(note,parent.lastElementChild);

		const box = this.newEl("div");
		box.className = `${block}__box`;
		note.appendChild(box);

		const content = this.newEl("div");
		content.className = `${block}__content`;
		box.appendChild(content);

		const _icon = this.newEl("div");
		_icon.className = `${block}__icon`;
		content.appendChild(_icon);

		const iconSVG = this.newEl("svg",xmlnsSVG);
		iconSVG.setAttribute("class",`${block}__icon-svg`);
		iconSVG.setAttribute("role","img");
		iconSVG.setAttribute("aria-label",icon);
		iconSVG.setAttribute("width","32px");
		iconSVG.setAttribute("height","32px");
		_icon.appendChild(iconSVG);

		const iconUse = this.newEl("use",xmlnsSVG);
		iconUse.setAttributeNS(xmlnsUse,"href",`#${icon}`);
		iconSVG.appendChild(iconUse);

		const text = this.newEl("div");
		text.className = `${block}__text`;
		content.appendChild(text);

		const _title = this.newEl("div");
		_title.className = `${block}__text-title`;
		_title.textContent = title;
		text.appendChild(_title);
	

		if (subtitle) {
			const _subtitle = this.newEl("div");
			_subtitle.className = `${block}__text-subtitle`;
			_subtitle.textContent = subtitle;
			text.appendChild(_subtitle);
		}
			
		if (reminder_date) {
			const _reminder_date = this.newEl("div");
			_reminder_date.className = `${block}__text-reminder`;
			_reminder_date.textContent = reminder_date;
			text.appendChild(_reminder_date);
		}
	

		const btns = this.newEl("div");
		btns.className = `${block}__btns`;
		box.appendChild(btns);

		actions.forEach(action => {
			const btn = this.newEl("button");
			btn.className = `${block}__btn`;
			btn.type = "button";
			btn.setAttribute("data-dismiss",id);

			const btnText = this.newEl("span");
			btnText.className = `${block}__btn-text`;
			btnText.textContent = action;

			btn.appendChild(btnText);
			btns.appendChild(btn);
		});

		this.el = note;
		this.id = note.id;
	}
	newEl(elName,NSValue) {
		if (NSValue)
			return document.createElementNS(NSValue,elName);
		else
			return document.createElement(elName);
	}
}



